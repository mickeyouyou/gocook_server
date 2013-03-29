<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel,   
    Zend\Authentication\AuthenticationService,
    Zend\Authentication\Adapter\AdapterInterface,
        
    Doctrine\ORM\EntityManager,
    DoctrineModule\Authentication\Adapter\DoctrineObjectRepository as DoctrineAdapter,
        
    User\Entity\User,  
    User\Entity\UserInfo,  
    User\Form\LoginForm,
    User\Service\UserService,
    User\Form\RegisterForm,
    User\Form\RegisterFilter,
    User\Form\ChangePassForm,
    User\Form\ChangePassFilter;    

use Zend\View\Model\JsonModel;


class UserController extends AbstractActionController 
{
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    protected $userRepository;
    

    public function indexAction()
    {
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if(!$authService->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('action' => 'login'));      
        }

        return new ViewModel(array(
            'username' => $authService->getIdentity()->__get('email')
        ));
    }
    

    public function loginAction()
    {   
        $loginSuccess = false;
        
        $request = $this->getRequest();
      
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');      
        if($authService->hasIdentity()) {
            if ($this->isMobile($request))
                $loginSuccess = true;
            else
                return $this->redirect()->toRoute('user');                      
        }

        if ($request->isPost()) {
          
            $data = $request->getPost();
            
            $userService = $this->getServiceLocator()->get('user_service');
            if($userService->authenticate($data)) {
                if($this->isMobile($request))
                    $loginSuccess = true;
                else
                    return $this->redirect()->toRoute('user');
            }

            if(!$this->isMobile($request))
            {
                return new ViewModel(array(
                    'form' => new LoginForm(),
                    'errors' => "Login Errors"
                ));
            }
        }
        if(!$this->isMobile($request)){
            return new ViewModel(array(
                'form' => new LoginForm()
            ));
        }
        
        
        if ($loginSuccess)
        {
            $username = $authService->getIdentity()->__get('email');
            return new JsonModel(array(
                'result' => true,
                'errorcode' => 0,
                'username' => $username,
                'icon' => ''
            ));            
        }
        else {
            return new JsonModel(array(
                'result' => false,
                'errorcode' => 1,
                'username' => "",
                'icon' => ''
            ));              
        }
    }
    
    public function registerAction() 
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
          
            $data = $request->getPost();
            
            $form = new RegisterForm;
            $form->setInputFilter(new RegisterFilter);
            $form->setData($data);

            if($form->isValid()) {
                $userService = $this->getServiceLocator()->get('user_service');
                if($userService->register($data)) {
                    return $this->redirect()->toRoute('user/login');
                }
            }
            
            return new ViewModel(array(
                'form' => new RegisterForm(),
                'errors' => 'Register Error!'
            ));            
        }
        
        return new ViewModel(array(
            'form' => new RegisterForm()
        ));
    }
    
    public function logoutAction()
    {
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');      
        $authService->clearIdentity();
        return $this->redirect()->toRoute('user');      
    }
    
    public function changepassAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
          
            $data = $request->getPost();
            
            $form = new ChangePassForm;
            $form->setInputFilter(new ChangePassFilter);
            $form->setData($data);
            
            if($form->isValid()) {
                $userService = $this->getServiceLocator()->get('user_service');
                if($userService->changepass($data)) {
                    return new ViewModel(array(
                      'form' => new ChangePassForm(),
                      'result' => 'Change Success!'
                    ));
                }
            }
            
            return new ViewModel(array(
                'form' => new ChangePassForm(),
                'result' => 'Change Error!'
            ));  
        }
        return new ViewModel(array(
            'form' => new ChangePassForm(),
        ));          
    }
    
    public function isMobile($request)
    {
        $isMobile = false;
        $requestHeaders  = $request->getHeaders();
        if($requestHeaders->has('x-client-identifier'))
        {
          $xIdentifier = $requestHeaders->get('x-client-identifier')->getFieldValue();
          if($xIdentifier == 'Mobile')
          {
            $isMobile = true;
          }
        }
        return $isMobile;
    }
    
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
 
    public function getEntityManager()
    {
        if (null === $this->em)
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return $this->em;        
    } 
    
    public function getRepository()
    {
        if (null === $this->userRepository) 
            $this->userEntity = $this->getEntityManager()->getRepository('User\Entity\User');
        return $this->userRepository;
    }
}
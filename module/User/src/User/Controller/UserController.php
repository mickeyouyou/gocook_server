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
            'username' => $authService->getIdentity()->__get('display_name')
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
            $username = $authService->getIdentity()->__get('display_name');
            $avatar = $authService->getIdentity()->__get('portrait');
            if (!$avatar || $avatar=='')
                $avatar = '';
            else
                $avatar = 'images/avatars/'.$avatar;
            
            return new JsonModel(array(
                'result' => 0,
                'errorcode' => 0,
                'username' => $username,
                'icon' => $avatar
            ));            
        }
        else {
            return new JsonModel(array(
                'result' => 1,
                'errorcode' => 1,
                'username' => "",
                'icon' => ''
            ));              
        }
    }
    
    public function registerAction() 
    {
        $result = 1;
        $errorcode = 0;
        
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');      
        $userService = $this->getServiceLocator()->get('user_service');

        $request = $this->getRequest();
        if ($request->isPost()) {
          
            $data = $request->getPost();
            
            $form = new RegisterForm;
            $form->setInputFilter(new RegisterFilter);
            $form->setData($data);
            
            if($form->isValid()) {
                $reg_result = $userService->register($data);
                if ($reg_result == 0)
                {
                    $result = $reg_result;
                    $errorcode = 0;
                }
                else
                {
                    $result = 1;
                    $errorcode = $reg_result;
                }
                if (!$this->isMobile($request))
                    return $this->redirect()->toRoute('user/login');
            }
            else 
            {
                if ($this->isMobile($request))
                {
                   $result = 1;
                    $errorcode = 1;//TODO:判断是哪种错误码                    
                }
                else
                {
                    return new ViewModel(array(
                        'form' => new RegisterForm(),
                        'errors' => 'Register Error!'
                    ));  
                }
            }
          
        }
        
        if (!$this->isMobile($request))
        {
            return new ViewModel(array(
                'form' => new RegisterForm()
            ));                 
        }

        if ($result == 0)
        {
            $File = $this->params()->fromFiles('avatar');
            if ($File)
                $userService->saveAvatar($File, $authService->getIdentity()->__get('user_id'));
            
            $username = $authService->getIdentity()->__get('display_name');
            $avatar = $authService->getIdentity()->__get('portrait');
            if (!$avatar || $avatar=='')
                $avatar = '';
            else
                $avatar = 'images/avatars/'.$avatar;
            
            return new JsonModel(array(
                'result' => 0,
                'errorcode' => 0,
                'username' => $username,
                'icon' => $avatar
            ));            
        }
        else
        {
            return new JsonModel(array(
                'result' => 1,
                'errorcode' => $errorcode,
                'username' => "",
                'icon' => ''
            ));              
        }
    }
    
    public function logoutAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');      
        $authService->clearIdentity();
        if (!$this->isMobile($request))
        {
            return $this->redirect()->toRoute('user');
        }
        else
        {
            return new JsonModel(array(
                'result' => 1,
                'errorcode' => 0
            ));
        }

    }
    
    public function changepassAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
          
            $data = $request->getPost();
            
            $form = new ChangePassForm;
            $form->setInputFilter(new ChangePassFilter);
            $form->setData($data);
            
            if($form->isValid()) {
                if (!$this->isMobile($request))
                {
                    $userService = $this->getServiceLocator()->get('user_service');
                    if($userService->changepass($data)) {
                        return new ViewModel(array(
                            'form' => new ChangePassForm(),
                            'result' => 'Change Success!'
                        ));
                    }
                }
                else
                {
                    $result = 0;
                }
            }

            if (!$this->isMobile($request))
            {
                return new ViewModel(array(
                    'form' => new ChangePassForm(),
                    'result' => 'Change Error!'
                ));
            }
        }

        if (!$this->isMobile($request))
        {
            return new ViewModel(array(
                'form' => new ChangePassForm(),
            ));
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode
        ));
    }

    public function basicinfoAction()
    {
        $result = 1;
        $errorcode = 0;
        
        $request = $this->getRequest();
        //如果是post，那么就根据提交的修改个人信息
        if ($request->isPost()) {


        }

    }


    /*************Others****************/
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
        if (null == $this->em)
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return $this->em;        
    } 
    
    public function getRepository()
    {
        if (null == $this->userRepository)
            $this->userRepository = $this->getEntityManager()->getRepository('User\Entity\User');
        return $this->userRepository;
    }
}
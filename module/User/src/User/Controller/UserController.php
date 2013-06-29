<?php

namespace User\Controller;
use Application\Controller\BaseAbstractActionController,
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
    User\Form\ChangeUserInfoForm,
    User\Form\ChangeUserInfoFilter,
    User\Form\ChangePassFilter;

use Zend\View\Model\JsonModel;


class UserController extends BaseAbstractActionController
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    protected $userRepository;

    // just for test
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

    // 登录
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

    // 注册
    public function registerAction()
    {
        $result = 1;
        $errorcode = 0;

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $userService = $this->getServiceLocator()->get('user_service');

        $request = $this->getRequest();
        if ($request->isPost()) {

            $data = $request->getPost();

            // var_dump($data);

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

    // 登出
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

    // 换密码
    public function changepassAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity() && $request->isPost()) {

            $data = $request->getPost();

            $form = new ChangePassForm;
            $form->setInputFilter(new ChangePassFilter);
            $form->setData($data);

            if($form->isValid()) {
                $userService = $this->getServiceLocator()->get('user_service');
                if($userService->changepass($data)) {
                    $result = 0;
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode
        ));
    }

    // 个人信息
    public function basicinfoAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            $user_id = $authService->getIdentity()->__get('user_id');
            $nickname = $authService->getIdentity()->__get('display_name');
            $portrait = $authService->getIdentity()->__get('portrait');
            if (!$portrait || $portrait=='')
                $portrait = '';
            else
                $portrait = 'images/avatars/'.$portrait;

            $gender = $authService->getIdentity()->__get('gender');
            $age = $authService->getIdentity()->__get('age');
            $career = $authService->getIdentity()->__get('career');
            $city = $authService->getIdentity()->__get('city');
            $province = $authService->getIdentity()->__get('province');
            $tel = $authService->getIdentity()->__get('tel');
            $intro = $authService->getIdentity()->__get('intro');

            $result_info = array(
                'userid' => $user_id,
                'nickname' => $nickname,
                'avatar' => $portrait,
                'gender' => $gender,
                'age' => $age,
                'career' => $career,
                'city' => $city,
                'province' => $province,
                'tel' => $tel,
                'intro' => $intro,
            );


            $result = 0;

            return new JsonModel(array(
                'result' => $result,
                'result_user_info' => $result_info,
            ));
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
        ));
    }

    // 修改个人信息
    public function changebasicinfoAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            if ($request->isPost()) {

                $data = $request->getPost();

                $form = new ChangeUserInfoForm();
                $form->setInputFilter(new ChangeUserInfoFilter);
                $form->setData($data);

                if($form->isValid()) {

                    $userService = $this->getServiceLocator()->get('user_service');
                    $change_result = $userService->changeuserinfo($data);
                    if($change_result == 0)
                        $result = 0;
                    else
                    {
                        $result = 1;
                        $errorcode = $change_result;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
        ));
    }

    //修改头像
    public function changeavatarAction()
    {
        $result = 1;
        $errorcode = 0;
        $icon = '';

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            if ($request->isPost()) {
                $userService = $this->getServiceLocator()->get('user_service');
                $File = $this->params()->fromFiles('avatar');
                if ($File)
                {
                    $save_result = $userService->saveAvatar($File, $authService->getIdentity()->__get('user_id'));
                    if ($save_result == 0)
                    {
                        $result = 0;
                        $icon = $authService->getIdentity()->__get('portrait');
                    }
                    else
                    {
                        $result = 1;
                        $errorcode = $save_result;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
            'avatar' => $icon,
        ));
    }


    /*************Others****************/
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
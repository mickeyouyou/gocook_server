<?php

namespace User\Controller;
use App\Controller\BaseAbstractActionController,
    Zend\View\Model\ViewModel,
    Doctrine\ORM\EntityManager,
    DoctrineModule\Authentication\Adapter\DoctrineObjectRepository as DoctrineAdapter,
    User\Form\LoginForm,
    User\Form\RegisterForm,
    User\Form\RegisterFilter,
    User\Form\ChangeUserInfoForm,
    User\Form\ChangeUserInfoFilter;

use App\Lib\GCFlag;
use Omega\Common\Common;
use Zend\View\Model\JsonModel;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

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

    /**************************************************************
     *
     * 登录
     * @post_params login password
     * @return result errorcode username user_id icon
     * @access public
     *
     *************************************************************/
    public function loginExAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($authService->hasIdentity()) {
            $result = GCFlag::GC_Success;
            $error_code = GCFlag::GC_NoErrorCode;
        } else {
            if ($request->isPost()) {
                $post_content = $request->getPost();

                $data = $post_content['data'];
                $rnd = $post_content['rnd'];

                if (!$data) {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                } else {
                    $userService = $this->getServiceLocator()->get('user_service');
                    $auth_result = $userService->authenticate_ex($data, $rnd);

                    $result = $auth_result[0];
                    $error_code = $auth_result[1];
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        }

        if ($result == GCFlag::GC_Success)
        {
            $username = $authService->getIdentity()->__get('display_name');
            $avatar = $authService->getIdentity()->__get('portrait');
            if (!$avatar || $avatar=='')
                $avatar = '';
            else
                $avatar = 'images/avatars/'.$avatar;

            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'user_id' => intval($authService->getIdentity()->__get('user_id')),
                'username' => $username,
                'icon' => $avatar
            ));
        }
        else {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'username' => "",
                'icon' => ''
            ));
        }
    }


    /**************************************************************
     *
     * 登录
     * @post_params login password
     * @return result errorcode username user_id icon
     * @access public
     *
     *************************************************************/
    public function loginAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($authService->hasIdentity()) {
            if ($this->isMobile($request)) {
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
            }
            else {
                return $this->redirect()->toRoute('user');
            }
        } else {
            if ($request->isPost()) {
                $data = $request->getPost();
                $userService = $this->getServiceLocator()->get('user_service');
                $auth_result = $userService->authenticate($data);
                $result = $auth_result[0];
                $error_code = $auth_result[1];

                if(!$this->isMobile($request)) {
                    if($auth_result[0] == GCFlag::GC_Success) {
                        return $this->redirect()->toRoute('user');
                    } else {
                        return new ViewModel(array(
                            'form' => new LoginForm(),
                            'errors' => $auth_result[0]
                        ));
                    }
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }

            if(!$this->isMobile($request)){
                return new ViewModel(array(
                    'form' => new LoginForm()
                ));
            }
        }

        if ($result == GCFlag::GC_Success)
        {
            $username = $authService->getIdentity()->__get('display_name');
            $avatar = $authService->getIdentity()->__get('portrait');
            if (!$avatar || $avatar=='')
                $avatar = '';
            else
                $avatar = 'images/avatars/'.$avatar;

            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'user_id' => intval($authService->getIdentity()->__get('user_id')),
                'username' => $username,
                'icon' => $avatar
            ));
        }
        else {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'username' => "",
                'icon' => ''
            ));
        }
    }

    /**************************************************************
     *
     * 注册
     * @post_params password tel email(option) nickname
     * @return result errorcode username user_id icon
     * @access public
     *
     *************************************************************/
    public function registerAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

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

                $result = $reg_result[0];
                $error_code = $reg_result[1];

                if (!$this->isMobile($request)) {
                    return $this->redirect()->toRoute('user');
                }
            }
            else
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;

                if (!$this->isMobile($request)) {
                    return new ViewModel(array(
                        'form' => new RegisterForm(),
                        'errors' => $error_code
                    ));
                }
            }

        } else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoPost;
        }

        if (!$this->isMobile($request))
        {
            return new ViewModel(array(
                'form' => new RegisterForm()
            ));
        }

        if ($result == GCFlag::GC_Success)
        {
            $File = $this->params()->fromFiles('avatar');
            if ($File)
                $userService->saveAvatar($File, $authService->getIdentity()->__get('user_id'));

            $username = $authService->getIdentity()->__get('display_name');
            $avatar = $authService->getIdentity()->__get('portrait');
            $user_id = $authService->getIdentity()->__get('user_id');
            if (!$avatar || $avatar=='')
                $avatar = '';
            else
                $avatar = 'images/avatars/'.$avatar;

            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'user_id' => $user_id,
                'username' => $username,
                'icon' => $avatar
            ));
        }
        else
        {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'username' => "",
                'icon' => ''
            ));
        }
    }

    /**************************************************************
     *
     * 登出
     * @return result errorcode
     * @access public
     *
     *************************************************************/
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
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode
            ));
        }
    }

//    // 换密码
//    public function changepassAction()
//    {
//        $result = 1;
//        $errorcode = 0;
//
//        $request = $this->getRequest();
//
//        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
//        if($this->isMobile($request) && $authService->hasIdentity() && $request->isPost()) {
//
//            $data = $request->getPost();
//
//            $form = new ChangePassForm;
//            $form->setInputFilter(new ChangePassFilter);
//            $form->setData($data);
//
//            if($form->isValid()) {
//                $userService = $this->getServiceLocator()->get('user_service');
//                if($userService->changepass($data)) {
//                    $result = 0;
//                }
//            }
//        }
//
//        return new JsonModel(array(
//            'result' => $result,
//            'errorcode' => $errorcode
//        ));
//    }

    /**************************************************************
     *
     * 获取个人信息
     * @return result errorcode result_user_info
     * @access public
     *
     *************************************************************/
    public function basicinfoAction()
    {
        $result = GCFlag::GC_Failed;
        $error_code = GCFlag::GC_CommonError;

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

            $result = GCFlag::GC_Success;
            $error_code = GCFlag::GC_NoErrorCode;
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'result_user_info' => $result_info,
            ));
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
            ));
        }
    }

    /**************************************************************
     *
     * 修改个人信息
     * @access public
     *
     *************************************************************/
    public function changebasicinfoAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

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
                    $result = $change_result[0];
                    $error_code = $change_result[1];
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else if (!$this->isMobile($request)){
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        } else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_AuthAccountInvalid;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    /**************************************************************
     *
     * 修改个人头像
     * @access public
     *
     *************************************************************/
    public function changeavatarAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $icon = '';

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            if ($request->isPost()) {
                $userService = $this->getServiceLocator()->get('user_service');
                $File = $this->params()->fromFiles('avatar');
                if ($File) {
                    $save_result = $userService->saveAvatar($File, $authService->getIdentity()->__get('user_id'));
                    if ($save_result[0] == GCFlag::GC_Success)
                    {
                        $result = $save_result[0];
                        $error_code = $save_result[1];
                        $icon = $authService->getIdentity()->__get('portrait');
                    }
                    else
                    {
                        $result = $save_result[0];
                        $error_code = $save_result[1];
                    }
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_NoPostAvatarFile;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else if (!$this->isMobile($request)){
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        } else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_AuthAccountInvalid;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
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
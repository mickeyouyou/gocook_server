<?php

namespace User\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use User\Entity\User;
use User\Entity\UserInfo;
use User\Form\LoginForm;
use User\Form\LoginFilter;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;
use Zend\Http\Request;
use Zend\Http\Client;
use User\Form\RegisterForm;
use User\Form\RegisterFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use App\Lib\Common;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

class UserService implements ServiceManagerAwareInterface, LoggerAwareInterface
{
    //const PASSWORDCOST = 14;

    protected $serviceManager;
    protected $entityManager;
    protected $logger;

    public function authenticate($data)
    {

        $this->doSomething();

        $form = new LoginForm;
        $form->setInputFilter(new LoginFilter());
        $form->setData($data);

        if(!$form->isValid()) {
            return false;
        }
        
        $identity_types = array('email');//'username');//目前只有email，将来开放username
        foreach ($identity_types as $type){
            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
            $adapter = $authService->getAdapter();
            $adapter->setOptions(array(
                'objectManager'=>$this->getEntityManager(),
                'identityClass'=>'User\Entity\User',
                'identityProperty'=>$type,
                'credentialProperty'=>'password',
                'credential_callable' => function(\User\Entity\User $user, $password) {
                    $bcrypt = new \Zend\Crypt\Password\Bcrypt();
                    $bcrypt->setCost(UserService::PASSWORDCOST);
                    return $bcrypt->verify($password, $user->__get('password'));
                },
            ));

            $adapter->setIdentityValue($data['login']);
            $password = $data['password'];
            $adapter->setCredentialValue($password);
            $authResult = $authService->authenticate();
            
            if ($authResult->isValid())
            {

                $authNamespace = new Container(Session::NAMESPACE_DEFAULT);
                $authNamespace->getManager()->rememberMe(60 * 60 * 24);

               // $authns = new \Zend\Session\Namespace($authService->getStorage()->getNamespace());

                // set an expiration on the Zend_Auth namespace where identity is held
                //$authns->setExpirationSeconds(60 * 30);  // expire auth storage after 30 min
                return true;
            }
        }

        return false;
    }
    
    
    public function register($data)
    {
        $result = 1;
        $error_code = 1;

        $user  = new User();
        $user->__set('password', '1');
        $user->__set('tel', $data['tel']);
        if (isset($data['email'])){
            $user->__set('email', $data['email']);
        }
        $user->__set('display_name', trim($data['nickname']));
        $user->__set('register_time', new \DateTime());

        $repository = $this->entityManager->getRepository('User\Entity\User');
        $tel_result = $repository->findOneBy(array('tel' => $data['tel']));
        $display_result = $repository->findOneBy(array('display_name' => $data['nickname']));
        if ($tel_result)
        {
            $error_code = 101;
            return array($result, $error_code);
        }
        if($display_result)
        {
            $error_code = 102;
            return array($result, $error_code);
        }

        $account = (string)$data['tel'];
        $token = $data['password'];
        $login_info = '{"Account":"'. $account .'","Password":"' . $token . '"}';

        $post_array = array();
        $post_array['Cmd'] = Common::REGISTER_CMD;
        $post_array['Data'] = addslashes($login_info);
        $post_array['Md5'] = Common::EncryptAppReqData(Common::REGISTER_CMD, $login_info);

        $post_str = json_encode($post_array);

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(Common::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {

            echo ($reg_response->getBody());

            $res_content = $reg_response->getBody();
            $res_json = json_decode($res_content);

            if ($res_json['Flag'] == Common::M6FLAG_Success) {

                $msix_id = intval($res_json['Data']);
                if ($msix_id == 0) {
                    $error_code = 103;
                    return array($result, $error_code);
                }


                $user->__set('', $res_json['Flag']);


                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $user_info = new UserInfo();
                $user_info->__set('collect_count', 0);
                $user_info->__set('dish_count', 0);
                $user_info->__set('recipe_count', 0);
                $user_info->__set('following_count', 0);
                $user_info->__set('followed_count', 0);

                $user->__set('user_info', $user_info);
                $user_info->__set('user', $user);

                $this->entityManager->persist($user_info);
                $this->entityManager->flush();

                $login_data = array('login' => $data['email'], 'password' => $data['password']);
                $this->authenticate($login_data);

                $result = 0;
                return array($result, $error_code);
            } else if ($res_json['Flag'] == -2){


            }

        } else {
            $error_code = 103;
            return array($result, $error_code);
        }
    }

    //保存头像
    public function saveAvatar($file, $uid)
    {
        $size = new \Zend\Validator\File\Size(array('min'=>1000)); //minimum bytes filesize
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setValidators(array($size), $file['name']);
        if (!$adapter->isValid()){
            return false;
//            $dataError = $adapter->getMessages();
//            $error = array();
//            foreach($dataError as $key=>$row)
//            {
//                $error[] = $row;
//            }
        } else {

            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
            $user = $authService->getIdentity();

            $curFullPath = '';
            if ($user->__get('portrait') != '')
            {
                $curFullPath = INDEX_ROOT_PATH."/public/images/avatars/".$user->__get('portrait');
            }

            $savedfilename = $uid.date("_YmdHim").'.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/avatars/".$savedfilename;
            @unlink($savedFullPath);
            $cpresult = copy($_FILES['avatar']['tmp_name'], $savedFullPath);
            @unlink($_FILES['avatar']['tmp_name']);

            if (!$cpresult)
                return 2;

            $user->__set('portrait', $savedfilename);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if ($curFullPath)
            {
                @unlink($curFullPath);
            }
            
            return 0;
            
//            $adapter->setDestination(INDEX_ROOT_PATH."/public/images/avatars");
//            if ($adapter->receive($file['name'])) {
//                return true;
//            }
        }       
    }
    
    public function changepass($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');     
        $user = $authService->getIdentity();
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(self::PASSWORDCOST);
        if ($bcrypt->verify($data['oripassword'], $user->__get('password')))
        {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost(self::PASSWORDCOST);
            $user->__set('password', $bcrypt->create($data['password']));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function changeuserinfo($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user = $authService->getIdentity();

        $repository = $this->entityManager->getRepository('User\Entity\User');

        $is_data_changed = false;

        if (isset($data['nickname']) && $data['nickname']!='')
        {
            $display_result = $repository->findOneBy(array('display_name' => $data['nickname']));
            if ($display_result)
            {
                return 2;
            }

            $user->__set('display_name', $data['nickname']);
            $is_data_changed = true;
        }

        if (isset($data['gender']) && $data['gender']!='')
        {
            $user->__set('gender', $data['gender']);
            $is_data_changed = true;
        }

        if (isset($data['age']) && $data['age']!='')
        {
            $user->__set('age', $data['age']);
            $is_data_changed = true;
        }

        if (isset($data['career']) && $data['career']!='')
        {
            $user->__set('career', $data['career']);
            $is_data_changed = true;
        }

        if (isset($data['tel']) && $data['tel']!='')
        {
            $user->__set('tel', $data['tel']);
            $is_data_changed = true;
        }

        if (isset($data['city']) && $data['city']!='')
        {
            $user->__set('city', $data['city']);
            $is_data_changed = true;
        }

        if (isset($data['province']) && $data['province']!='')
        {
            $user->__set('province', $data['province']);
            $is_data_changed = true;
        }

        if (isset($data['intro']) && $data['intro']!='')
        {
            $user->__set('intro', $data['intro']);
            $is_data_changed = true;
        }

        if ($is_data_changed)
        {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return 0;
        }


        return 1;

    }

    
    /*************Manager****************/
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
    
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
        return $this;
    }
    
    public function getEntityManager()
    {
        return $this->entityManager;      
    }


    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function doSomething()
    {
        // Do your work

        if (null !== $this->logger) {
            $this->logger->info('Something done here!');

           // $this->logger->err('This is an error!');

            $this->logger->warn('This is not an error!');

        }
    }
}

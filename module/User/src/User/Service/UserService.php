<?php

namespace User\Service;

use App\Lib\CommonDef;
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
    protected $serviceManager;
    protected $entityManager;
    protected $logger;

    /**************************************************************
     *
     * 认证
     * @param array data
     * @return array($result, $error_code) 返回结果
     * @access public
     *
     *************************************************************/
    public function authenticate($data)
    {
        var_dump($data);

        $form = new LoginForm;
        $form->setInputFilter(new LoginFilter());
        $form->setData($data);

        if(!$form->isValid()) {
            $result = CommonDef::GC_Failed;
            $error_code = CommonDef::GC_PostInvalid;
            return array($result, $error_code);
        }

        $account = (string)$data['login'];
        $token = $data['password'];
        $login_info = '{"Account":"'. $account .'","Password":"' . $token . '"}';

        $post_array = array();
        $post_array['Cmd'] = CommonDef::AUTH_CMD;
        $post_array['Data'] = addslashes($login_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::AUTH_CMD, $login_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        $this->logger->info($post_str);


        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
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
            $this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();
            $res_json = json_decode($res_content, true);

            if (intval($res_json['Flag']) == CommonDef::M6FLAG_Success) {
                $msix_id = intval($res_json['Data']);
                // 校验M6服务器数据
                if ($msix_id == 0) {
                    $result = CommonDef::GC_Failed;
                    $error_code = CommonDef::GC_M6ServerError;
                    return array($result, $error_code);
                }

                $repository = $this->entityManager->getRepository('User\Entity\User');
                // 到GC的数据库中查找
                $msixid_result = $repository->findOneBy(array('msix_id' => $msix_id));
                if ($msixid_result) {
                    // 登录
                    $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
                    $adapter = $authService->getAdapter();
                    $adapter->setOptions(array(
                        'objectManager'=>$this->getEntityManager(),
                        'identityClass'=>'User\Entity\User',
                        'identityProperty'=>'msix_id',
                        'credentialProperty'=>'password',
                        'credential_callable' => function(\User\Entity\User $user, $password) {
                            return $password == $user->__get('password');
                        },
                    ));

                    $adapter->setIdentityValue($msix_id);
                    $password = 1;
                    $adapter->setCredentialValue($password);
                    $authResult = $authService->authenticate();

                    if ($authResult->isValid())
                    {
                        $authNamespace = new Container(Session::NAMESPACE_DEFAULT);
                        $authNamespace->getManager()->rememberMe(60 * 60 * 24);

                        $result = CommonDef::GC_Success;
                        $error_code = CommonDef::GC_NoErrorCode;
                        return array($result, $error_code);
                    } else {
                        $result = CommonDef::GC_Failed;
                        $error_code = CommonDef::GC_CommonError;//理论上不应该会走到这里的
                        return array($result, $error_code);
                    }
                } else {
                    // 注册
                    $user  = new User();
                    $user->__set('password', '1');
                    $user->__set('tel', $account);

                    $nickname = 'u' . substr(md5(date("YmdHis")),8,16). '_' . $account;
                    $user->__set('display_name', $nickname);
                    $user->__set('register_time', new \DateTime());

                    $user->__set('msix_id', $msix_id);
                    $user->__set('msix_access_token', $token);

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

                    // 自动登录
                    $login_data = array('login' => $account, 'password' => $token);
                    $this->authenticate($login_data);

                    //返回成功
                    $result = CommonDef::GC_Success;
                    $error_code = CommonDef::GC_NoErrorCode;
                    return array($result, $error_code);
                }
            } else if (intval($res_json['Flag']) == CommonDef::M6FLAG_Auth_ActInvalid){
                $result = CommonDef::GC_Failed;
                $error_code = CommonDef::GC_AccountNotExist;
                return array($result, $error_code);
            } else if (intval($res_json['Flag'])  == CommonDef::M6FLAG_Auth_PswInvalid){
                $result = CommonDef::GC_Failed;
                $error_code = CommonDef::GC_PasswordInvalid;
                return array($result, $error_code);
            } else {
                $result = CommonDef::GC_Failed;
                $error_code = CommonDef::GC_M6ServerError;
                return array($result, $error_code);
            }
        } else {
            // 甲方服务器4XX，5XX
            $result = CommonDef::GC_Failed;
            $error_code = CommonDef::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }

    /**************************************************************
     *
     * 注册
     * @param array data
     * @return array($result, $error_code) 返回结果
     * @access public
     *
     *************************************************************/
    public function register($data)
    {
        $result = CommonDef::GC_Failed;
        $error_code = CommonDef::GC_NoErrorCode;

        $user  = new User();
        $user->__set('password', '1');
        $user->__set('tel', $data['tel']);
        if (isset($data['email'])){
            $user->__set('email', $data['email']);
        }
        $user->__set('display_name', trim($data['nickname']));
        $user->__set('register_time', new \DateTime());

        $repository = $this->entityManager->getRepository('User\Entity\User');
        $display_result = $repository->findOneBy(array('display_name' => $data['nickname']));
        if($display_result)//检查昵称重复
        {
            $result = CommonDef::GC_Failed;
            $error_code = CommonDef::GC_NickNameExist;
            return array($result, $error_code);
        }

        $account = (string)$data['tel'];
        $token = $data['password'];
        $login_info = '{"Account":"'. $account .'","Password":"' . $token . '"}';

        $post_array = array();
        $post_array['Cmd'] = CommonDef::REGISTER_CMD;
        $post_array['Data'] = addslashes($login_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::REGISTER_CMD, $login_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        $this->logger->info($post_str);


        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
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
            $this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();
            $res_json = json_decode($res_content, true);

            if (intval($res_json['Flag']) == CommonDef::M6FLAG_Success) {
                $msix_id = intval($res_json['Data']);
                // 校验M6服务器数据
                if ($msix_id == 0) {
                    $result = CommonDef::GC_Failed;
                    $error_code = CommonDef::GC_M6ServerError;
                    return array($result, $error_code);
                }

                $user->__set('msix_id', $msix_id);
                $user->__set('msix_access_token', $token);

                $this->logger->info($msix_id);
                $this->logger->info($token);

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

                // 自动登录
                $login_data = array('login' => $account, 'password' => $token);
                $this->authenticate($login_data);

                //返回成功
                $result = CommonDef::GC_Success;
                $error_code = CommonDef::GC_NoErrorCode;
                return array($result, $error_code);
            } else if (intval($res_json['Flag']) == CommonDef::M6FLAG_Reg_ActExist){
                $result = CommonDef::GC_Failed;
                $error_code = CommonDef::GC_AccountExist;
                return array($result, $error_code);
            } else {
                $result = CommonDef::GC_Failed;
                $error_code = CommonDef::GC_M6ServerError; // M6服务器返回结果
                return array($result, $error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = CommonDef::GC_Failed;
            $error_code = CommonDef::GC_M6ServerConnError;
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

    /**************************************************************
     *
     *	使用特定function对数组中所有元素做处理
     *	@param	string	&$array		要处理的字符串
     *	@param	string	$function	要执行的函数
     *	@return boolean	$apply_to_keys_also		是否也应用到key上
     *	@access public
     *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                if (is_string($value))
                {
                    $array[$key] = $function($value);
                }
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}

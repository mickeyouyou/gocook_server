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
use User\Form\RegisterForm;
use User\Form\RegisterFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class UserService implements ServiceManagerAwareInterface
{
    const PASSWORDCOST = 14;

    protected $serviceManager;
    protected $entityManager;
    
    public function authenticate($data)
    {        
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
                return true;
            }
        }

        return false;
    }
    
    
    public function register($data)
    {
        $user  = new User();

        $bcrypt = new Bcrypt;
        $bcrypt->setCost(self::PASSWORDCOST);
        $user->__set('password', $bcrypt->create($data['password']));
        $user->__set('email', $data['email']);
        if (trim($data['nickname'])!="")
          $user->__set('display_name', trim($data['nickname']));
        $user->__set('register_time', new \DateTime());
                
        $repository = $this->entityManager->getRepository('User\Entity\User');
        $email_result = $repository->findOneBy(array('email' => $data['email']));
        $display_result = $repository->findOneBy(array('display_name' => $data['nickname']));
        if ($email_result)
            return 2;//errorcode
        if($display_result)
            return 3;//errorcode

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

        return 0;//result
    }

    //保存头像
    public function saveAvatar($file, $uid)
    {
        $size = new \Zend\Validator\File\Size(array('min'=>20000)); //minimum bytes filesize         
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
                
            $savedfilename = $uid.date("_YmdHim").'.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/avatars/".$savedfilename;
            @unlink($savedFullPath);
            $cpresult = copy($_FILES['avatar']['tmp_name'], $savedFullPath);
            @unlink($_FILES['avatar']['tmp_name']);

            if (!$cpresult)
                return false;
            
            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');     
            $user = $authService->getIdentity();
            $user->__set('portrait', $savedfilename);
            $this->entityManager->persist($user);
            $this->entityManager->flush();    
            
            return true;
            
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
}

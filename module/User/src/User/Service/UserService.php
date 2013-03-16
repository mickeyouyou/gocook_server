<?php

namespace User\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use User\Entity\User;
use User\Form\LoginForm;
use User\Form\RegisterForm;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class UserService implements ServiceManagerAwareInterface
{
    const PASSWORDCOST = 14;

    protected $serviceManager;
    protected $entityManager;
    
    public function authenticate($data)
    {
        $form = new LoginForm;
        $form->setData($data);

        if(!$form->isValid()) {
            return false;
        }
        
        $identity_types = array('email','username');
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
        
        $repository = $this->entityManager->getRepository('User\Entity\User');
        $email_result = $repository->findOneBy(array('email' => $data['email']));
        //$display_result = $repository->findOneBy(array('display_name' => $data['display_name']));
        if (!$email_result) // && !$display_result)
        {
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

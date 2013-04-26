<?php

namespace Main\Service;

use Main\Entity\DishComment;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Main\Entity\Dish;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class DishService implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    protected $entityManager;


    // 发表dish评论
    public function commitOnDish($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');
        $dish_id = intval($data['dish_id']);
        $dish_repository = $this->entityManager->getRepository('Main\Entity\Dish');
        $dish = $dish_repository->findBy(array('dish_id' => $dish_id));
        if ($dish)
        {
            $recipe_comment = new DishComment();
            $recipe_comment->__set('create_time', new \DateTime());
            $recipe_comment->__set('user_id', $user_id);
            $recipe_comment->__set('dish_id', $dish_id);
            $recipe_comment->__set('recipe_id', $dish->__get('recipe_id'));
            $recipe_comment->__set('content', $data['content']);
            $this->entityManager->persist($recipe_comment);
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

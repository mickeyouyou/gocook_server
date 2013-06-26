<?php

namespace Main\Service;

use Main\Entity\DishComment;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Main\Entity\Dish;
use User\Entity\User;
use Main\Repository\DishRepository;
use Main\Entity\Recipe;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class DishService implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    protected $entityManager;


    // 根据日期排序
    public function getDishesByDate($recipe_id, $limit, $offset=0)
    {
        $dishes = $this->entityManager->getRepository('Main\Entity\Dish')->getDishesByCreateDate($recipe_id, $limit, $offset);
        return $dishes;
    }

    // 根据好评排序
    public function getDishesByFavorCount($recipe_id, $limit, $offset=0)
    {
        $dishes = $this->entityManager->getRepository('Main\Entity\Dish')->getDishesByFavorCount($recipe_id, $limit, $offset);
        return $dishes;
    }

    // 组装dish
    public function packetDishes($dishes)
    {
        if ($dishes)
        {
            $result_dishes = array();
            foreach ($dishes as $dish){

                $repository = $this->getEntityManager()->getRepository('User\Entity\User');
                $dish_user = $repository->findOneBy(array('user_id' => $dish->__get('user_id')));

                $result_dish = array(
                    'dish_id' => $dish->__get('dish_id'),
                    'recipe_id' => $dish->__get('recipe_id'),
                    'user_id' => $dish->__get('user_id'),
                    'user_name' => $dish_user->__get('display_name'),
                    'user_avatar' => $dish_user->__get('portrait'),
                    'create_time' => $dish->create_time==null?'':$dish->create_time,
                    'content' => $dish->__get('content'),
                    'photo_img' => 'images/dish/140/'.$dish->__get('photo_img'),
                    'favor_count' => $dish->__get('favor_count')
                );

                array_push($result_dishes, $result_dish);
            }
            return $result_dishes;
        }
        return null;
    }


    // 发表dish评论
    public function commitOnDish($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');
        $dish_id = intval($data['dish_id']);
        $dish_repository = $this->entityManager->getRepository('Main\Entity\Dish');
        $dish = $dish_repository->findOneBy(array('dish_id' => $dish_id));
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


    // 生成dish
    public function postOneDish($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');
        $recipe_id = intval($data['recipe_id']);
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');
        $recipe = $recipe_repository->findOneBy(array('recipe_id' => $recipe_id));
        if ($recipe)
        {
            $recipe = new Dish();
            $recipe->__set('create_time', new \DateTime());
            $recipe->__set('user_id', $user_id);
            $recipe->__set('recipe_id', $recipe_id);
            $recipe->__set('content', $data['content']);

            $saved_filename = $recipe_id.$user_id.date("_YmdHim").'.png';
            $saved_fullpath = INDEX_ROOT_PATH."/public/images/dish/".$saved_filename;
            @unlink($saved_fullpath);
            $cp_result = copy($_FILES['photo_img']['tmp_name'], $saved_fullpath);
            @unlink($_FILES['photo_img']['tmp_name']);

            if (!$cp_result)
                return false;

            $recipe->__set('photo_img', $saved_filename);

            $this->entityManager->persist($recipe);
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

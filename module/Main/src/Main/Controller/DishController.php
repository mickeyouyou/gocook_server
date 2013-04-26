<?php

/**
 * Recipe IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Main\Form\DishCommentForm;
use Main\Form\DishCommentFilter;

class DishController extends AbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;


    public function indexAction() {

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $repository = $this->getEntityManager()->getRepository('Main\Entity\Dish');
            if ($request->isGet() && $this->params()->fromQuery('id')!='') {

                $recipe_id = intval($this->params()->fromQuery('id'));
                $dishes = $repository->findBy(array('recipe_id' => $recipe_id));
                if ($dishes)
                {
                    $result_dishes = array();
                    foreach ($dishes as $dish){

                        $repository = $this->getEntityManager()->getRepository('User\Entity\User');
                        $dish_user = $repository->findBy(array('user_id' => $dish->__get('user_id')));

                        $result_dish = array(
                            'dish_id' => $dish->__get('dish_id'),
                            'recipe_id' => $dish->__get('recipe_id'),
                            'user_id' => $dish->__get('user_id'),
                            'user_name' => $dish_user->__get('display_name'),
                            'create_time' => $dish->create_time==null?'':$dish->create_time,
                            'content' => $dish->__get('content'),
                            'photo_img' => 'images/dish/140/'.$dish->__get('photo_img'),
                            'favor_count' => $dish->__get('favor_count')
                        );

                        array_push($result_dishes, $result_dish);
                    }

                    return new JsonModel(array(
                        'result' => 0,
                        'result_dishes' => $result_dishes,
                    ));
                }
            }
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }


    public function commentAction()
    {
        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {
            if ($request->isPost())
            {
                $data = $request->getPost();

                $form = new DishCommentForm;
                $form->setInputFilter(new DishCommentFilter);
                $form->setData($data);

                if($form->isValid()) {
                    $recipeService = $this->getServiceLocator()->get('dish_service');
                    if ($recipeService->commitOnDish($form->getData()))
                    {
                        return new JsonModel(array(
                            'result' => 0,
                        ));
                    }

                }
            }
        }

        return new JsonModel(array(
            'result' => 1,
        ));
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

}

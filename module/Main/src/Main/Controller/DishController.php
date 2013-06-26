<?php

/**
 * Dish IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Application\Controller\BaseAbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Main\Form\DishCommentForm;
use Main\Form\DishCommentFilter;
use Main\Form\DishPostForm;
use Main\Form\DishPostFilter;

class DishController extends BaseAbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;


    public function indexAction() {

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            if ($request->isGet() && $this->params()->fromQuery('id')!='') {

                $page = 1;
                if ($this->params()->fromQuery('page')&&$keyword=$this->params()->fromQuery('page')!='')
                {
                    $page = intval($this->params()->fromQuery('page'));
                }

                $recipe_id = intval($this->params()->fromQuery('id'));

                $dishService = $this->getServiceLocator()->get('dish_service');
                $dishes = $dishService->getDishesByFavorCount($recipe_id, 10, ($page-1)*10);
                if ($dishes)
                {
                    $result_dishes = $dishService->packetDishes($dishes);
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
                    $dishService = $this->getServiceLocator()->get('dish_service');
                    if ($dishService->commitOnDish($form->getData()))
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


    public function postAction()
    {
        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {
            if ($request->isPost())
            {
                $data = $request->getPost();

                $form = new DishPostForm;
                $form->setInputFilter(new DishPostFilter);
                $form->setData($data);

                if($form->isValid()) {
                    $dishService = $this->getServiceLocator()->get('dish_service');
                    if ($dishService->postOneDish($form->getData()))
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

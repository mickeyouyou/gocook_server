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

class RecipeController extends AbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function indexAction() {

        $request = $this->getRequest();
        $repository = $this->getEntityManager()->getRepository('Main\Entity\Recipe');
        if ($request->isGet() && $this->params()->fromQuery('id')!='') {

            $recipe_id = intval($this->params()->fromQuery('id'));
            $recipe = $repository->findOneBy(array('recipe_id' => $recipe_id));
            if ($recipe)
            {
                return new JsonModel(array(
                    'result' => 0,
                    'result_recipe' => array(
                        'recipe_id' => $recipe->recipe_id,

                    ),
                ));
            }
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    public function topnewAction()
    {
        $recipeService = $this->getServiceLocator()->get('recipe_service');
        $page = 1;
        if ($this->params()->fromQuery('page')&&$keyword=$this->params()->fromQuery('keyword')!='')
        {
            $page = intval($this->params()->fromQuery('page'));
        }

        $recipes = $recipeService->getTopNewRecipes(10, ($page - 1)*10);

        $result_recipes = array();
        foreach ($recipes as $recipe){
            $result_recipe = array(
                'recipe_id' => $recipe->__get('recipe_id'),
                'name' => $recipe->__get('name'),
                'image' => 'images/recipe/140/'.$recipe->__get('cover_img'),
                'dish_count' => $recipe->__get('dish_count')
            );

            array_push($result_recipes, $result_recipe);
        }

        return new JsonModel(array(
            'result' => 0,
            'result_recipes' => $result_recipes,
        ));
    }

    public function tophotAction()
    {
        $recipeService = $this->getServiceLocator()->get('recipe_service');
        $page = 1;
        if ($this->params()->fromQuery('page')&&$keyword=$this->params()->fromQuery('keyword')!='')
        {
            $page = intval($this->params()->fromQuery('page'));
        }

        $recipes = $recipeService->getTopCollectedRecipes(10, ($page - 1)*10);

        $result_recipes = array();
        foreach ($recipes as $recipe){
            $result_recipe = array(
                'recipe_id' => $recipe->__get('recipe_id'),
                'name' => $recipe->__get('name'),
                'image' => 'images/recipe/140/'.$recipe->__get('cover_img'),
                'dish_count' => $recipe->__get('dish_count')
            );

            array_push($result_recipes, $result_recipe);
        }

        return new JsonModel(array(
            'result' => 0,
            'result_recipes' => $result_recipes,
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

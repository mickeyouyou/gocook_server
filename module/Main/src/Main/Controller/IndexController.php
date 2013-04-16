<?php

/**
 * Main IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Main\Repository\RecipeRepository;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController {

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postDispatch'), -100);
    }

    public function postDispatch()
    {

    }
  
    public function indexAction() {
        $result = new JsonModel(array(
	        'some_parameter' => 'some value',
            'success'=>true,
        ));
 
        return $result;
    }

    public function iosMainAction() {
        $topnew_img = 'images/recipe/140/265058.1.jpg';

        $recipeService = $this->getServiceLocator()->get('recipe_service');
        $topRecipe = $recipeService->getTopCollectedRecipe();
        $tophot_img = 'images/recipe/140/'.$topRecipe->cover_img;

        $recommend_items = array();
        $recommend_keywords = array('家常菜','猪肉','快手菜','汤羹');
        foreach ($recommend_keywords as $keyword){
            $recipes = $recipeService->getRecipesByKeywordOfCatgory($keyword, 4, 0);
            $recommend_item = array();
            $recommend_item['name'] = $keyword;
            $recommend_item['images'] = array();
            foreach ($recipes as $recipe){
                array_push($recommend_item['images'], 'images/recipe/140/'.$recipe->__get('cover_img'));
            }
            array_push($recommend_items, $recommend_item);
        }
        $result = new JsonModel(array(
            'topnew_img' => $topnew_img,
            'tophot_img' => $tophot_img,
            'recommend_items' => $recommend_items,
        ));

        return $result;

    }


}

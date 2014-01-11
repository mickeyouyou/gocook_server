<?php

/**
 * Main IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use App\Controller\BaseAbstractActionController;
use App\Lib\CommonDef;
use App\Lib\GCFlag;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use App\Lib\Common;
use Zend\Http\Request;
use Zend\Http\Client;
use App\Lib\Cryptogram;
use Zend\View\Model\ViewModel;

class IndexController extends BaseAbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

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

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $topnew_img = 'images/recipe/140/265058.1.jpg';

            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $topRecipe = $recipeService->getTopLikeRecipe();
            $tophot_img = 'images/recipe/140/'.$topRecipe->cover_img;

            $recommend_items = array();
            $recommend_keywords = array('家常菜','猪肉','快手菜','汤羹','鱼','夏日菜');
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
            return new JsonModel(array(
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'topnew_img' => $topnew_img,
                'tophot_img' => $tophot_img,
                'recommend_items' => $recommend_items,
            ));

        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
    }

    public function searchAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $recipeService = $this->getServiceLocator()->get('recipe_service');
            if ($request->isGet() && $this->params()->fromQuery('keyword') && $this->params()->fromQuery('keyword')!='') {

                $keyword = $this->params()->fromQuery('keyword');

                $page = 1;
                if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
                {
                    $page = intval($this->params()->fromQuery('page'));
                }

                $recipes = $recipeService->getReicpesByAutoSearch($keyword, 10, ($page - 1)*10);

                $result_recipes = array();
                foreach ($recipes as $recipe){
                    $result_recipe = array(
                        'recipe_id' => $recipe->__get('recipe_id'),
                        'name' => $recipe->__get('name'),
                        'image' => 'images/recipe/140/'.$recipe->__get('cover_img'),
                        'materials' => $recipe->materials,
                        'collected_count' => $recipe->__get('collected_count')
                    );

                    array_push($result_recipes, $result_recipe);
                }

                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => GCFlag::GC_NoErrorCode,
                    'result_recipes' => $result_recipes,
                ));

            } else {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => GCFlag::GC_GetParamInvalid,
                ));
            }
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
    }


	public function androidUpdateAction()
	{
		return new JsonModel(array(
               'version' => 1113,
               'url' => '',
        ));
	}

    public function shareAction()
    {
        $result = GCFlag::GC_Failed;
        $result_array = array();

        $request = $this->getRequest();

        $repository = $this->getEntityManager()->getRepository('Main\Entity\Recipe');
        if ($request->isGet() && $this->params()->fromQuery('id')!='') {
            $recipe_id = intval($this->params()->fromQuery('id'));
            $recipe = $repository->findOneBy(array('recipe_id' => $recipe_id));
            if ($recipe)
            {
                $result_array = array(
                        'recipe_id' => $recipe->recipe_id,
                        'author_id' => $recipe->user->user_id,//不知道为啥user_id拿来是string，大概是doctrine的bug
                        'author_name' => $recipe->user->display_name,
                        'recipe_name' => $recipe->name,
                        'intro' => $recipe->description,
                        'collected_count' => $recipe->collected_count,
                        'like_count' => $recipe->like_count,
                        'dish_count' => $recipe->dish_count,
                        'comment_count' => $recipe->comment_count,
                        'cover_image' => '/images/recipe/526/'.$recipe->__get('cover_img'),
                        'materials' => $recipe->materials,
                        'steps' => $recipe->recipe_steps,
                        'tips' => $recipe->tips,
                );
                $result = GCFlag::GC_Success;
            } else {
                $result = GCFlag::GC_Failed;
            }
        } else {
            $result = GCFlag::GC_Failed;
        }

        if ($result == GCFlag::GC_Failed) {
            $viewModel = new ViewModel( array (
                'result' => $result,
                'steps' => array(),
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        } else {
            $steps = json_decode($result_array['steps'],true);

            $viewModel = new ViewModel( array (
                'result' => $result,
                'result_array' => $result_array,
                'materials' => explode('|',$result_array['materials']),
                'steps' => $steps['steps'],
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
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

        if (is_array($array)) {
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
        }
        $recursive_counter--;
    }
}

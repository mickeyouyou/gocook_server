<?php

/**
 * Recipe IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Application\Controller\BaseAbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

use Main\Form\RecipeCommentForm,
    Main\Form\RecipeCommentFilter,
    Main\Form\RecipePostForm,
    Main\Form\RecipePostFilter;

class RecipeController extends BaseAbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function indexAction() {

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $repository = $this->getEntityManager()->getRepository('Main\Entity\Recipe');
            $collect_repository = $this->getEntityManager()->getRepository('Main\Entity\UserCollection');
            $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            if ($request->isGet() && $this->params()->fromQuery('id')!='') {

                $recipe_id = intval($this->params()->fromQuery('id'));
                $recipe = $repository->findOneBy(array('recipe_id' => $recipe_id));
                //查找收藏状态
                $collect  = 1;
                if ($authService->hasIdentity())
                {
                    $user_id = $authService->getIdentity()->__get('user_id');
                    $user_collect = $collect_repository->findOneBy(array('user_id'=>$user_id, 'recipe_id' => $recipe_id));
                    if ($user_collect)
                    {
                        $collect = 0;
                    }
                }

                if ($recipe)
                {
                    $steps_array = Json::decode($recipe->recipe_steps, Json::TYPE_ARRAY);
                    // var_dump($steps_array);
                    return new JsonModel(array(
                        'result' => 0,
                        'result_recipe' => array(
                            'recipe_id' => $recipe->recipe_id,
                            'author_id' => $recipe->user->user_id,//不知道为啥user_id拿来是string，大概是doctrine的bug
                            'author_name' => $recipe->user->display_name,
                            'recipe_name' => $recipe->name,
                            'intro' => $recipe->description,
                            'collected_count' => $recipe->collected_count,
                            'dish_count' => $recipe->dish_count,
                            'comment_count' => $recipe->comment_count,
                            'cover_image' => 'images/recipe/526/'.$recipe->__get('cover_img'),
                            'materials' => $recipe->materials,
                            'steps' => $steps_array['steps'],
                            'tips' => $recipe->tips,
                            'collect' => $collect,
                        ),
                    ));
                }
            }
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    public function topnewAction()
    {
        $request = $this->getRequest();
        if ($this->isMobile($request))
        {
            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $recipes = $recipeService->getTopNewRecipes(10, ($page - 1)*10);

            $result_recipes = array();
            foreach ($recipes as $recipe){
                $result_recipe = array(
                    'recipe_id' => $recipe->__get('recipe_id'),
                    'name' => $recipe->__get('name'),
                    'materials' => $recipe->materials,
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

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    public function tophotAction()
    {
        $request = $this->getRequest();
        if ($this->isMobile($request))
        {
            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $recipes = $recipeService->getTopCollectedRecipes(10, ($page - 1)*10);

            $result_recipes = array();
            foreach ($recipes as $recipe){
                $result_recipe = array(
                    'recipe_id' => $recipe->__get('recipe_id'),
                    'name' => $recipe->__get('name'),
                    'materials' => $recipe->materials,
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
        return new JsonModel(array(
            'result' => 1,
        ));
    }

    // 返回所有评论
    public function commentsAction()
    {
        $request = $this->getRequest();
        if ($this->isMobile($request))
        {
            //返回所有的评论
            if ($this->params()->fromQuery('recipe_id')&&$this->params()->fromQuery('recipe_id')!='')
            {
                $recipe_id = intval($this->params()->fromQuery('recipe_id'));

                $repository = $this->getEntityManager()->getRepository('Main\Entity\RecipeComment');
                $recipe_comments = $repository->findBy(array('recipe_id' => $recipe_id), array('create_time' => 'DESC'));
                if ($recipe_comments)
                {
                    $result_recipe_comments = array();
                    foreach ($recipe_comments as $recipe_comment){

                        $avatar = $recipe_comment->user->__get('portrait');
                        if (!$avatar || $avatar=='')
                            $avatar = '';
                        else
                            $avatar = 'images/avatars/'.$avatar;

                        $result_recipe_comment = array(
                            'user_id' => intval($recipe_comment->user->user_id),
                            'name' => $recipe_comment->user->display_name,
                            'portrait' => $avatar,
                            'content' => $recipe_comment->content,
                            'create_time' => $recipe_comment->create_time==null?'':$recipe_comment->create_time,
                        );

                        array_push($result_recipe_comments, $result_recipe_comment);
                    }

                    return new JsonModel(array(
                        'result' => 0,
                        'result_recipe_comments' => $result_recipe_comments,
                    ));
                }
            }
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    // 发布一个菜谱
    public function createAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {
            if ($request->isPost())
            {
                $data = $request->getPost();

                $form = new RecipePostForm;
                $form->setInputFilter(new RecipePostFilter);
                $form->setData($data);

                if ($form->isValid()) {

                    $recipeService = $this->getServiceLocator()->get('recipe_service');
                    $save_result = $recipeService->saveRecipe($data);

                    if ($save_result == 0)
                    {
                        $result = 0;
                    }
                    else
                    {
                        $result = 1;
                        $errorcode = $save_result;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
        ));
    }

    // 修改菜谱
    public function editAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {
            if ($request->isPost())
            {
                $data = $request->getPost();

                $form = new RecipePostForm;
                $form->setInputFilter(new RecipeCommentFilter);
                $form->setData($data);

                if ($form->isValid()) {
                    $recipeService = $this->getServiceLocator()->get('recipe_service');
                    $save_result = $recipeService->saveRecipe($data);

                    if ($save_result == 0)
                    {
                        $result = 0;
                    }
                    else
                    {
                        $result = 1;
                        $errorcode = $save_result;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
        ));
    }

    // 评论菜谱
    public function commentAction()
    {
        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {
            if ($request->isPost())
            {
                $data = $request->getPost();

                $form = new RecipeCommentForm;
                $form->setInputFilter(new RecipeCommentFilter);
                $form->setData($data);

                if($form->isValid()) {
                    $recipeService = $this->getServiceLocator()->get('recipe_service');
                    if ($recipeService->commitOnRecipe($data))
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

    // 上传临时封面图片
    public function uploadCoverPhotoAction()
    {
        $result = 1;
        $errorcode = 0;
        $icon = '';

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            if ($request->isPost()) {
                $recipeService = $this->getServiceLocator()->get('recipe_service');
                $File = $this->params()->fromFiles('cover');
                if ($File)
                {
                    $save_result = $recipeService->uploadTmpCoverPicture($File);
                    if ($save_result != "")
                    {
                        $result = 0;
                        $icon = $save_result;
                    }
                    else
                    {
                        $result = 1;
                        $errorcode = $save_result;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
            'avatar' => $icon,
        ));
    }

    // 上传临时步骤图片
    public function uploadStepPhotoAction()
    {
        $result = 1;
        $errorcode = 0;
        $icon = '';

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if($this->isMobile($request) && $authService->hasIdentity()) {

            if ($request->isPost()) {
                $recipeService = $this->getServiceLocator()->get('recipe_service');
                $File = $this->params()->fromFiles('step');
                if ($File)
                {
                    $save_result = $recipeService->uploadTmpStepPicture($File);
                    if ($save_result != "")
                    {
                        $result = 0;
                        $icon = $save_result;
                    }
                    else
                    {
                        $result = 1;
                        $errorcode = 1;
                    }
                }
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
            'avatar' => $icon,
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

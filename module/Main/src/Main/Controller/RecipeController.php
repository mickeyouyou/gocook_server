<?php

/**
 * Recipe IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use App\Controller\BaseAbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use App\Lib\GCFlag;

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

        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $repository = $this->getEntityManager()->getRepository('Main\Entity\Recipe');
            $collect_repository = $this->getEntityManager()->getRepository('Main\Entity\UserCollection');
            $like_repository = $this->getEntityManager()->getRepository('Main\Entity\UserLike');
            $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            if ($request->isGet() && $this->params()->fromQuery('id')!='') {

                $recipe_id = intval($this->params()->fromQuery('id'));
                $recipe = $repository->findOneBy(array('recipe_id' => $recipe_id));
                //查找收藏状态
                $collect  = GCFlag::E_NotCollected;
                if ($authService->hasIdentity())
                {
                    $user_id = $authService->getIdentity()->__get('user_id');
                    $user_collect = $collect_repository->findOneBy(array('user_id'=>$user_id, 'recipe_id' => $recipe_id));
                    if ($user_collect)
                    {
                        $collect = GCFlag::E_IsCollected;
                    }
                }

                //查找赞状态
                $like  = GCFlag::E_UnLiked;
                if ($authService->hasIdentity())
                {
                    $user_id = $authService->getIdentity()->__get('user_id');
                    $user_like = $like_repository->findOneBy(array('user_id'=>$user_id, 'recipe_id' => $recipe_id));
                    if ($user_like)
                    {
                        $like = GCFlag::E_Liked;
                    }
                }

                if ($recipe)
                {
                    $steps_array = Json::decode($recipe->recipe_steps, Json::TYPE_ARRAY);
                    // var_dump($steps_array);
                    return new JsonModel(array(
                        'result' => GCFlag::GC_Success,
                        'errorcode' => GCFlag::GC_NoErrorCode,
                        'result_recipe' => array(
                            'recipe_id' => $recipe->recipe_id,
                            'author_id' => $recipe->user->user_id,//不知道为啥user_id拿来是string，大概是doctrine的bug
                            'author_name' => $recipe->user->display_name,
                            'recipe_name' => $recipe->name,
                            'intro' => $recipe->description,
                            'collected_count' => $recipe->collected_count,
                            'like_count' => $recipe->like_count,
                            'dish_count' => $recipe->dish_count,
                            'comment_count' => $recipe->comment_count,
                            'cover_image' => 'images/recipe/526/'.$recipe->__get('cover_img'),
                            'materials' => $recipe->materials,
                            'steps' => $steps_array['steps'],
                            'tips' => $recipe->tips,
                            'collect' => $collect,
                            'like' => $like,
                        ),
                    ));
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_RecipeNotExist;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_GetParamInvalid;
            }
        }  else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    public function topnewAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

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
                    'image' => 'images/recipe/300/'.$recipe->__get('cover_img'),
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
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    public function tophotAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();
        if ($this->isMobile($request))
        {
            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $recipes = $recipeService->getTopLikeRecipes(10, ($page - 1)*10);

            $result_recipes = array();
            foreach ($recipes as $recipe){
                $result_recipe = array(
                    'recipe_id' => $recipe->__get('recipe_id'),
                    'name' => $recipe->__get('name'),
                    'materials' => $recipe->materials,
                    'image' => 'images/recipe/300/'.$recipe->__get('cover_img'),
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
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    // 返回所有评论
    public function commentsAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

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
                        'result' => GCFlag::GC_Success,
                        'result_recipe_comments' => $result_recipe_comments,
                    ));
                } else {
                    return new JsonModel(array(
                        'result' => GCFlag::GC_Success,
                        'errorcode' => GCFlag::GC_NoErrorCode,
                        'result_recipe_comments' => array(),
                    ));                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_GetParamInvalid;
            }
        } else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    // 发布一个菜谱
    public function createAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $credit = 0;

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
                    $result_array = $recipeService->saveRecipe($data);
                    $result = $result_array[0];
                    $error_code = $result_array[1];
                    $credit = $result_array[2];
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
            'credit' => $credit,
        ));
    }

    // 修改菜谱
    public function editAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $credit = 0;

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
                    $result_array = $recipeService->saveRecipe($data);
                    $result = $result_array[0];
                    $error_code = $result_array[1];
                    $credit = $result_array[2];
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
            'credit' => $credit,
        ));
    }

    // 删除菜谱
    public function deleteAction() {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $credit = 0;

        $request = $this->getRequest();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authService->hasIdentity()&&$this->isMobile($request))
        {

            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $recipe_id = -1;
            if ($this->params()->fromQuery('recipe_id')&&$this->params()->fromQuery('recipe_id')!='')
            {
                $recipe_id = intval($this->params()->fromQuery('recipe_id'));
            }

            if ($recipe_id != -1)
            {
                $result_array = $recipeService->delRecipe($recipe_id);
                $result = $result_array[0];
                $error_code = $result_array[1];
                $credit = $result_array[2];
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_GetParamInvalid;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
            'credit' => $credit,
        ));


    }

    // 评论菜谱
    public function commentAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

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
                            'result' => GCFlag::GC_Success,
                            'errorcode' => GCFlag::GC_NoErrorCode,
                        ));
                    } else {
                        $result = GCFlag::GC_Failed;
                        $error_code = GCFlag::GC_CommentOnRecipeFailed;
                    }
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
        ));
    }

    // 上传临时封面图片
    public function uploadCoverPhotoAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
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
                        $result = GCFlag::GC_Success;
                        $error_code = GCFlag::GC_NoErrorCode;
                        $icon = $save_result;
                    }
                    else
                    {
                        $result = GCFlag::GC_Failed;
                        $error_code = $save_result;
                    }
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
            'avatar' => $icon,
        ));
    }

    // 上传临时步骤图片
    public function uploadStepPhotoAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
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
                        $result = GCFlag::GC_Success;
                        $error_code = GCFlag::GC_NoErrorCode;
                        $icon = $save_result;
                    }
                    else
                    {
                        $result = GCFlag::GC_Failed;
                        $error_code = $save_result;
                    }
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_PostInvalid;
                }
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoPost;
            }
        } else {
            if (!$this->isMobile($request))
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_NoMobileDevice;
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_AuthAccountInvalid;
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $error_code,
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

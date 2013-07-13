<?php

namespace Main\Service;

use Main\Entity\RecipeComment;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use Main\Entity\Recipe;
use Zend\ServiceManager\ServiceLocatorAwareInterface;


class RecipeService implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    protected $entityManager;
    

    // 获取收藏次数最多的一个菜谱
    public function getTopCollectedRecipe()
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->getRecipesByCollectedCount(1,0);
        $top_recipe = $recipes[0];
        return $top_recipe;
    }

    // 获取收藏次数最多的菜谱
    public function getTopCollectedRecipes($limit, $offset=0)
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->getRecipesByCollectedCount($limit,$offset);
        return $recipes;
    }

    // 获取最新的菜谱
    public function getTopNewRecipes($limit, $offset=0)
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->getRecipesByCreateDate($limit,$offset);
        return $recipes;
    }

    // 根据keyword查找catgory
    public function getRecipesByKeywordOfCatgory($keyword, $limit, $offset=0)
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->findRecipesByCatgory($keyword, $limit, $offset);
        return $recipes;
    }

    // 根据keyword查找catgory, name, materials
    public function getReicpesByAutoSearch($keyword, $limit, $offset=0)
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->findRecipeByAutoSearch($keyword, $limit, $offset);
        return $recipes;
    }

    // 发表recipe评论（留言）
    public function commitOnRecipe($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = intval($authService->getIdentity()->__get('user_id'));
        $recipe_id = intval($data['recipe_id']);
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');
        $recipe = $recipe_repository->findOneBy(array('recipe_id' => $recipe_id));
        if ($recipe)
        {
            $recipe->__set('comment_count', $recipe->__get('comment_count')+1);

            $recipe_comment = new RecipeComment();
            $recipe_comment->__set('create_time', new \DateTime());
            $recipe_comment->__set('user_id', $user_id);
            $recipe_comment->__set('recipe_id', $recipe_id);
            $recipe_comment->__set('content', $data['content']);

            $recipe_comment->__set('recipe', $recipe);
            $recipe_comment->__set('user', $authService->getIdentity());

            $this->entityManager->persist($recipe_comment);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    // 创建/修改菜谱
    public function saveRecipe($data)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        $recipe = null;
        $is_create = false;


        //判断是创建还是修改
        if (isset($data['reicpe_id']) && $data['recipe_id'] != '') {
            $recipe = $recipe_repository->findOneBy(array('recipe_id' => $data['recipe_id']));
        }

        if ($recipe == null) {
            $is_create = true;
            $recipe = new Recipe();
        }

        // 封面
        if ($is_create)
        {

        }

        if (isset($data['name']) && $data['name']!='')
        {
            $recipe->__set('name', $data['name']);
        }

        if (isset($data['desc']))
        {
            $recipe->__set('desc', $data['desc']);
        }

        if (isset($data['category']))
        {
            $recipe->__set('catgory', $data['category']);
        }

        if (isset($data['materials']) && $data['materials']!='')
        {



        }

        if (isset($data['recipe_steps']) && $data['recipe_steps']!='')
        {



        }

        if (isset($data['tips']))
        {
            $recipe->__set('tips', $data['tips']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return 0;
    }


    //保存头像
    public function saveCoverPicture($file)
    {
        $size = new \Zend\Validator\File\Size(array('min'=>1000)); //minimum bytes filesize
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setValidators(array($size), $file['name']);
        if (!$adapter->isValid()){
            return false;
//            $dataError = $adapter->getMessages();
//            $error = array();
//            foreach($dataError as $key=>$row)
//            {
//                $error[] = $row;
//            }
        } else {

            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
            $user = $authService->getIdentity();

            $curFullPath = '';
            if ($user->__get('portrait') != '')
            {
                $curFullPath = INDEX_ROOT_PATH."/public/images/avatars/".$user->__get('portrait');
            }

            $savedfilename = $uid.date("_YmdHim").'.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/avatars/".$savedfilename;
            @unlink($savedFullPath);
            $cpresult = copy($_FILES['avatar']['tmp_name'], $savedFullPath);
            @unlink($_FILES['avatar']['tmp_name']);

            if (!$cpresult)
                return 2;

            $user->__set('portrait', $savedfilename);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if ($curFullPath)
            {
                @unlink($curFullPath);
            }

            return 0;

//            $adapter->setDestination(INDEX_ROOT_PATH."/public/images/avatars");
//            if ($adapter->receive($file['name'])) {
//                return true;
//            }
        }
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

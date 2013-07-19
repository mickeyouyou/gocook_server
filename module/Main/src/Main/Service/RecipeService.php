<?php

namespace Main\Service;

use Main\Entity\RecipeComment;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\Json\Json;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use Main\Entity\Recipe;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use User\Entity\User;

require_once __DIR__.'/Zebra_Image.php';

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
        $user_id = intval($authService->getIdentity()->__get('user_id'));

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
            $recipe->__set('user_id', $user_id);

            $user = $authService->getIdentity();
            $recipe->__set('user', $user);

            $recipe->__set('create_time', new \DateTime());
        }
        else
        {
            if ($recipe->__get('user_id') != $user_id)
            {
                return 1;
            }
        }

        if (isset($data['name']) && $data['name']!='')
        {
            $recipe->__set('name', $data['name']);
        }
        else if ($is_create)
        {
            return 1;
        }

        if (isset($data['desc']))
        {
            $recipe->__set('description', $data['desc']);
        }

        if (isset($data['category']))
        {
            $recipe->__set('catgory', $data['category']);
        }

        if (isset($data['materials']) && $data['materials']!='')
        {
            //检查meterials
            $materials = $data['materials'];
            $mate_array = explode('|', $materials);
            if (count($mate_array)%2 != 0)
            {
                return 1;
            }
            $recipe->__set('materials', $data['materials']);
        }
        else if ($is_create)
        {
            return 1;
        }

        if (isset($data['tips']) && $data['materials']!='')
        {
            $recipe->__set('tips', $data['tips']);
        }

        if (isset($data['steps']) && $data['steps']!='')
        {
            var_dump($data['steps']);

            $steps = Json::decode($data['steps'], Json::TYPE_ARRAY);

            $step_array = $steps['steps'];
            $index = 0;
            foreach ($step_array as $step){

                $step_img = $step['img'];
                if ($step_img != '')
                {
                    // 看看是否已经有了图片
                    $alreadyFullPath = INDEX_ROOT_PATH."/public/images/recipe/step/".$step_img;
                    if (!file_exists($alreadyFullPath))
                    {
                        $tmpFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$step_img;
                        if (file_exists($tmpFullPath))
                        {
                            // 处理临时文件
                            // create a new instance of the class
                            $image = new Zebra_Image();
                            $image->Zebra_Image();
                            $image->source_path = $tmpFullPath;

                            $image->preserve_aspect_ratio = true;
                            $image->enlarge_smaller_images = true;
                            $image->preserve_time = true;

                            $stepFullPath_200 = INDEX_ROOT_PATH."/public/images/recipe/step/".$step_img;
                            $image->target_path = $stepFullPath_200;
                            $image->resize(200, 0, ZEBRA_IMAGE_CROP_CENTER);

                            unlink($tmpFullPath);
                        }
                        else
                        {
                            $step_array[$index]['img'] = '';
                        }
                    }
                }

                $index++;
            }

            $steps['steps'] = $step_array;

            $this->arrayRecursive($steps, 'urlencode', false);
            $steps_str = urldecode(Json::encode($steps));

            $recipe->__set('recipe_steps', $steps_str);
        }
        else if ($is_create)
        {
            return 1;
        }

        // 最后处理图片
        // 封面
        // 判断是否带上图片上来了
        if (isset($data['cover_img']) && $data['name']!='')
        {
            $cover_img = $data['cover_img'];
            $tmpFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$cover_img;
            if (file_exists($tmpFullPath))
            {
                // 处理临时文件
                // create a new instance of the class
                $image = new Zebra_Image();
                $image->Zebra_Image();
                $image->source_path = $tmpFullPath;

                $image->preserve_aspect_ratio = true;
                $image->enlarge_smaller_images = true;
                $image->preserve_time = true;

                $coverFullPath_140 = INDEX_ROOT_PATH."/public/images/recipe/140/".$cover_img;
                $image->target_path = $coverFullPath_140;

                $result = $image->resize(140, 0, ZEBRA_IMAGE_CROP_CENTER);

                var_dump($tmpFullPath);

                var_dump($coverFullPath_140);

                $coverFullPath_526 = INDEX_ROOT_PATH."/public/images/recipe/526/".$cover_img;
                $image->target_path = $coverFullPath_526;
                $image->resize(526, 0, ZEBRA_IMAGE_CROP_CENTER);

                var_dump($tmpFullPath);

                unlink($tmpFullPath);

                $recipe->__set('cover_img', $cover_img);

            }
            else
                return 1;
        }
        else
        {
            if ($is_create)//如果是新创建，并且没有图片的话，则创建失败
            return 1;
        }

        $this->entityManager->persist($recipe);
        $this->entityManager->flush();
        return 0;
    }


    //保存cover
    public function uploadTmpCoverPicture($file)
    {
        $size = new \Zend\Validator\File\Size(array('min'=>1000)); //minimum bytes filesize
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setValidators(array($size), $file['name']);
        if (!$adapter->isValid()){
            return '';
        } else {

            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
            $user = $authService->getIdentity();

            $user_id = intval($authService->getIdentity()->__get('user_id'));
            $curFullPath = '';

            $savedfilename = $user_id.date("_YmdHim").'.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$savedfilename;
            @unlink($savedFullPath);
            $cpresult = copy($_FILES['cover']['tmp_name'], $savedFullPath);
            @unlink($_FILES['cover']['tmp_name']);

            if (!$cpresult)
                return '';

            if ($curFullPath)
            {
                @unlink($curFullPath);
            }

            return $savedfilename;
        }
    }

    //保存step
    public function uploadTmpStepPicture($file)
    {
        $size = new \Zend\Validator\File\Size(array('min'=>1000)); //minimum bytes filesize
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setValidators(array($size), $file['name']);
        if (!$adapter->isValid()){
            return '';
        } else {

            $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
            $user = $authService->getIdentity();

            $user_id = intval($authService->getIdentity()->__get('user_id'));

            $curFullPath = '';

            $savedfilename = $user_id.date("_YmdHim").'.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$savedfilename;
            @unlink($savedFullPath);
            $cpresult = copy($_FILES['step']['tmp_name'], $savedFullPath);
            @unlink($_FILES['step']['tmp_name']);

            if (!$cpresult)
                return '';

            if ($curFullPath)
            {
                @unlink($curFullPath);
            }

            return $savedfilename;
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
        $recursive_counter--;
    }
}

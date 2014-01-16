<?php

namespace Main\Service;

use App\Lib\GCFlag;
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
use Omega\Image\Zebra_Image;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

class RecipeService implements ServiceManagerAwareInterface, LoggerAwareInterface
{
    protected $serviceManager;
    protected $entityManager;
    protected $logger;

    // 获取收藏次数最多的一个菜谱
    public function getTopLikeRecipe()
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->getRecipesByLikeCount(1,0);
        $top_recipe = $recipes[0];
        return $top_recipe;
    }

    // 获取收藏次数最多的菜谱
    public function getTopLikeRecipes($limit, $offset=0)
    {
        $recipes = $this->entityManager->getRepository('Main\Entity\Recipe')->getRecipesByLikeCount($limit,$offset);
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
        $result = 1;
        $error_code = 1;

        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = intval($authService->getIdentity()->__get('user_id'));

        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        $recipe = null;
        $is_create = false;

        //判断是创建还是修改
        if (isset($data['recipe_id']) && intval($data['recipe_id']) != 0 && $data['recipe_id'] != '') {
            $recipe = $recipe_repository->findOneBy(array('recipe_id' => intval($data['recipe_id'])));
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
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_RecipeNotBelong2U;
                return array($result, $error_code);
            }
        }

        if (isset($data['name']) && $data['name']!='')
        {
            // 判断是否符合规则,2013,2014为破折号
            $name = trim($data['name']);
            if (!preg_match('/^[0-9a-zA-Z_\-\x{4e00}-\x{9fa5}\x{ff01}-\x{ff5e}\x{2014}\x{2013}]{2,30}$/u', $name)) {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_RecipeNameInvalid;
                return array($result, $error_code);
            }

            $recipe->__set('name', $name);
        }
        else if ($is_create)
        {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_RecipeNameInvalid;
            return array($result, $error_code);
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
            $materials = trim($data['materials']);
            $materials = str_replace(' ', '', $materials);

            $mate_array = explode('|', $materials);
            if (count($mate_array)%2 != 0)
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_RecipeMaterialInvalid;
                return array($result, $error_code);
            }
            $recipe->__set('materials', $data['materials']);
        }
        else if ($is_create)
        {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_RecipeMaterialInvalid;
            return array($result, $error_code);
        }


        if (isset($data['tips']) && $data['materials']!='')
        {
            $recipe->__set('tips', $data['tips']);
        }

        if (isset($data['steps']) && $data['steps']!='')
        {
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
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_RecipeStepInvalid;
            return array($result, $error_code);
        }


        // 最后处理图片
        // 封面
        // 判断是否带上图片上来了
        if (isset($data['cover_img']) && $data['name']!='')
        {
            $cover_img = $data['cover_img'];
            $tmpFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$cover_img;
            $coverFullPath = INDEX_ROOT_PATH."/public/images/recipe/526/".$cover_img;

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

                $coverFullPath_526 = INDEX_ROOT_PATH."/public/images/recipe/526/".$cover_img;
                $image->target_path = $coverFullPath_526;
                $image->resize(526, 0, ZEBRA_IMAGE_CROP_CENTER);

                $coverFullPath_300 = INDEX_ROOT_PATH."/public/images/recipe/300/".$cover_img;
                $image->target_path = $coverFullPath_300;
                $image->resize(300, 0, ZEBRA_IMAGE_CROP_CENTER);

                unlink($tmpFullPath);

                $recipe->__set('cover_img', $cover_img);

            }
            else if (file_exists($coverFullPath)) {
                $recipe->__set('cover_img', $cover_img);
            } else {
//                $result = 1;
//                $error_code = 408;
//                return array($result, $error_code);
            }
        }
        else
        {
            if ($is_create)//如果是新创建，并且没有图片的话，则创建失败
            {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_RecipeCoverInvalid;
                return array($result, $error_code);
            }
        }

        $recipe->__set('collected_count', 0);
        $recipe->__set('like_count', 0);
        $recipe->__set('dish_count', 0);
        $recipe->__set('comment_count', 0);
        $recipe->__set('browse_count', 0);


        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        if ($is_create) {
            $cookService = $this->getServiceManager()->get('cook_service');
            $cookService->addCredit($user_id, GCFlag::Credit_Normal);
            return array($result, $error_code, GCFlag::Credit_Normal);
        } else {
            return array($result, $error_code, 0);
        }
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

            $microSenond = floor(microtime()*10000);// 取一个毫秒级数字,4位。
            $savedfilename = $user_id.date("_YmdHis") . $microSenond . '.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$savedfilename;
            if (file_exists($savedFullPath))
                unlink($savedFullPath);
            $cpresult = copy($_FILES['cover']['tmp_name'], $savedFullPath);
            if (file_exists($_FILES['cover']['tmp_name']))
                unlink($_FILES['cover']['tmp_name']);

            if (!$cpresult)
                return '';

            if ($curFullPath)
            {
                if (file_exists($savedFullPath))
                    unlink($curFullPath);
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

            $microSenond = floor(microtime()*10000);// 取一个毫秒级数字,4位。
            $savedfilename = $user_id.date("_YmdHis"). $microSenond . '.png';
            $savedFullPath = INDEX_ROOT_PATH."/public/images/tmp/".$savedfilename;
            if (file_exists($savedFullPath))
                unlink($savedFullPath);
            $cpresult = copy($_FILES['step']['tmp_name'], $savedFullPath);
            if (file_exists($_FILES['step']['tmp_name']))
                unlink($_FILES['step']['tmp_name']);

            if (!$cpresult)
                return '';

            if ($curFullPath)
            {
                if (file_exists($curFullPath))
                    unlink($curFullPath);
            }

            return $savedfilename;
        }
    }

    public function delRecipe($recipe_id) {

        $result = 1;
        $errorcode = 1;
        $credit = 0;

        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = intval($authService->getIdentity()->__get('user_id'));

        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');
        $comment_repository = $this->entityManager->getRepository('Main\Entity\RecipeComment');

        $recipe = $recipe_repository->findOneBy(array('recipe_id' => $recipe_id));
        if ($recipe == null) {
            $result = GCFlag::GC_Failed;
            $errorcode = GCFlag::GC_RecipeNotExist;
        } else {
            if ($recipe->__get('user_id') == $user_id) {

                $comments = $comment_repository->findBy(array('recipe_id' => $recipe_id));
                foreach ($comments as $comment){
                    $this->entityManager->remove($comment);
                }
                $this->entityManager->flush();

                $this->entityManager->remove($recipe);
                $this->entityManager->flush();

                $result = GCFlag::GC_Success;
                $errorcode = GCFlag::GC_NoErrorCode;
                $credit = GCFlag::Credit_Normal;

                $cookService = $this->getServiceManager()->get('cook_service');
                $cookService->removeCredit($user_id, GCFlag::Credit_Normal);

            } else {
                $result = GCFlag::GC_Failed;
                $errorcode = GCFlag::GC_RecipeNotBelong2U;
            }
        }

        return array($result, $errorcode, $credit);
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

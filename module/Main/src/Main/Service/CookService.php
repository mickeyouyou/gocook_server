<?php

namespace Main\Service;

use App\Lib\Common;
use App\Lib\CommonDef;
use App\Lib\M6Flag;
use App\Lib\GCFlag;
use Main\Entity\UserLike;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Doctrine\ORM\EntityManager;
use Main\Entity\UserCollection;
use Main\Entity\UserRelation;
use Zend\Log\Logger;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Exception\RuntimeException;

class CookService implements ServiceManagerAwareInterface, LoggerAwareInterface
{
    protected $serviceManager;
    protected $entityManager;
    protected $logger;

    // 获取收藏的菜谱
    public function getMyCollection($limit, $offset=0)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserCollection');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');


        $result_recipes = array();

        $user_id_recipe_id_s = $repository->findBy(array('user_id' => $user_id), null, $limit, $offset);

        foreach ($user_id_recipe_id_s as $user_id_recipe_id){
            $tmp_recipe_id = $user_id_recipe_id->__get('recipe_id');
            $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $tmp_recipe_id));
            if ($tmp_recipe) {
                $result_recipe = array(
                    'recipe_id' => $tmp_recipe->__get('recipe_id'),
                    'name' => $tmp_recipe->__get('name'),
                    'materials' => $tmp_recipe->materials,
                    'image' => 'images/recipe/140/'.$tmp_recipe->__get('cover_img'),
                    'collected_count' => $tmp_recipe->__get('collected_count')
                );

                array_push($result_recipes, $result_recipe);
            }
        }
        return $result_recipes;
    }

    // 每次检测user info中的collection数目
    public function ResetUserInfoAllCollection($coll_count)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            if ($user_info->__get('collect_count') != $coll_count) {
                $user_info->__set('collect_count', $coll_count);
                $this->entityManager->persist($user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 获取我收藏的菜谱数
    public function  getAllMyCollCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserCollection u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        // 检测user info
        $this->ResetUserInfoAllCollection($count);

        return $count;
    }

    // 每次检测user info中的collection数目
    public function AddUserInfoAllCollection()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            $coll_count = $user_info->__get('collect_count') + 1;
            $user_info->__set('collect_count', $coll_count);
            $this->entityManager->persist($user_info);
            $this->entityManager->flush();
        }
    }

    // 加入收藏菜谱
    public function addMyCollection($collid)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserCollection');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'recipe_id' => $collid));
        if ($tmp_record)
            return GCFlag::GC_AlreadyCollectRecipe;

        //查找是否有该菜谱
        $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $collid));
        if ($tmp_recipe)
        {
            $this->addCredit($tmp_recipe->__get('user_id'), GCFlag::Credit_Normal);
            $coll_count = $tmp_recipe->__get('collected_count') + 1;
            $tmp_recipe->__set('collected_count', $coll_count);
            $this->entityManager->persist($tmp_recipe);
            $this->entityManager->flush();

            $this->AddUserInfoAllCollection();

            $user_collection = new UserCollection();
            $user_collection->__set('user_id', $user_id);
            $user_collection->__set('recipe_id', $collid);
            $this->entityManager->persist($user_collection);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        } else {
            return GCFlag::GC_RecipeNotExist;
        }
    }

    // 每次检测user info中的collection数目
    public function RemoveUserInfoAllCollection()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            $coll_count = $user_info->__get('collect_count') - 1;
            if ($coll_count < 0) {
                $coll_count = 0;
            }
            $user_info->__set('collect_count', $coll_count);
            $this->entityManager->persist($user_info);
            $this->entityManager->flush();
        }
    }

    // 删除收藏菜谱
    public function delMyCollection($coll_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserCollection');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');
        $relation_object = $repository->findOneBy(array('recipe_id' => $coll_id, 'user_id' => $user_id));

        if ($relation_object)
        {
            //查找是否有该菜谱
            $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $coll_id));
            if ($tmp_recipe) {
                $this->removeCredit($tmp_recipe->__get('user_id'), GCFlag::Credit_Normal);
                $coll_count = $tmp_recipe->__get('collected_count') - 1;
                if ($coll_count < 0) {
                    $coll_count = 0;
                }
                $tmp_recipe->__set('collected_count', $coll_count);
                $this->entityManager->persist($tmp_recipe);
                $this->entityManager->flush();

                $this->RemoveUserInfoAllCollection();
            }

            $this->entityManager->remove($relation_object);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        }
        return GCFlag::GC_NotMyCollectRecipe;
    }



    // 获取赞的菜谱
    public function getMyLike($limit, $offset=0)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserLike');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');


        $result_recipes = array();

        $user_id_recipe_id_s = $repository->findBy(array('user_id' => $user_id), null, $limit, $offset);

        foreach ($user_id_recipe_id_s as $user_id_recipe_id){
            $tmp_recipe_id = $user_id_recipe_id->__get('recipe_id');
            $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $tmp_recipe_id));
            if ($tmp_recipe) {
                $result_recipe = array(
                    'recipe_id' => $tmp_recipe->__get('recipe_id'),
                    'name' => $tmp_recipe->__get('name'),
                    'materials' => $tmp_recipe->materials,
                    'image' => 'images/recipe/140/'.$tmp_recipe->__get('cover_img'),
                    'collected_count' => $tmp_recipe->__get('collected_count')
                );

                array_push($result_recipes, $result_recipe);
            }
        }
        return $result_recipes;
    }

    // 获取赞的菜谱总数
    public function  getAllMyLikeCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserLike u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    // 赞一个菜谱
    public function addLike($like_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserLike');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'recipe_id' => $like_id));
        if ($tmp_record)
            return GCFlag::GC_AlreadyLikedRecipe;

        //查找是否有该菜谱
        $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $like_id));
        if ($tmp_recipe)
        {
            $this->addCredit($tmp_recipe->__get('user_id'), GCFlag::Credit_Normal);
            $coll_count = $tmp_recipe->__get('like_count') + 1;
            $tmp_recipe->__set('like_count', $coll_count);
            $this->entityManager->persist($tmp_recipe);
            $this->entityManager->flush();

            $user_like = new UserLike();
            $user_like->__set('user_id', $user_id);
            $user_like->__set('recipe_id', $like_id);
            $this->entityManager->persist($user_like);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        } else {
            return GCFlag::GC_RecipeNotExist;
        }
    }

    // 取消赞一个菜谱
    public function removeLike($like_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserLike');
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');
        $relation_object = $repository->findOneBy(array('recipe_id' => $like_id, 'user_id' => $user_id));

        if ($relation_object)
        {
            //查找是否有该菜谱
            $tmp_recipe = $recipe_repository->findOneBy(array('recipe_id' => $like_id));
            if ($tmp_recipe) {
                $this->removeCredit($tmp_recipe->__get('user_id'), GCFlag::Credit_Normal);
                $like_count = $tmp_recipe->__get('like_count') - 1;
                if ($like_count < 0) {
                    $like_count = 0;
                }
                $tmp_recipe->__set('like_count', $like_count);
                $this->entityManager->persist($tmp_recipe);
                $this->entityManager->flush();
            }

            $this->entityManager->remove($relation_object);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        }
        return GCFlag::GC_NotLikedRecipe;
    }


    // 获取我的菜谱
    public function getMyRecipes($limit, $offset=0)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        $result_recipes = array();

        $recipes = $recipe_repository->findBy(array('user_id' => $user_id), array('create_time' => 'DESC'), $limit, $offset);

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
        return $result_recipes;
    }

    // 每次检测user info中的recipe数目
    public function ResetUserInfoAllMyRecipes($recipe_count)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            if ($user_info->__get('recipe_count') != $recipe_count) {
                $user_info->__set('recipe_count', $recipe_count);
                $this->entityManager->persist($user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 获取我的菜谱数
    public function getAllMyRecipesCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.recipe_id) FROM Main\Entity\Recipe u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        $this->ResetUserInfoAllMyRecipes($count);

        return $count;
    }

    // 每次检测别人的user info中的recipe数目 ** 是否有必要？
    public function ResetUserInfoAllUserRecipes($user_id, $recipe_count)
    {
        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $user = $user_repository->findOneBy(array('user_id' => $user_id));
        if ($user) {
            $user_info = $user->__get('user_info');
            if ($user_info) {
                if ($user_info->__get('recipe_count') != $recipe_count) {
                    $user_info->__set('recipe_count', $recipe_count);
                    $this->entityManager->persist($user_info);
                    $this->entityManager->flush();
                }
            }
        }
    }

    // 获取某人的菜谱
    public function getUserRecipes($user_id, $limit, $offset=0)
    {
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        $result_recipes = array();

        $recipes = $recipe_repository->findBy(array('user_id' => $user_id), array('create_time' => 'DESC'), $limit, $offset);

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

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\Recipe u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        // 检测user info
        $this->ResetUserInfoAllUserRecipes($user_id, $count);

        return array($count, $result_recipes);
    }

    // 我关注的人
    public function getMyWatch($limit, $offset=0)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        $result_watches = array();

        $watch_id_s = $repository->findBy(array('user_id' => $user_id), array('id' => 'DESC'), $limit, $offset);

        foreach ($watch_id_s as $watch_id){
            $tmp_id = $watch_id->__get('target_id');
            $tmp_watch = $user_repository->findOneBy(array('user_id' => $tmp_id));
            if ($tmp_watch) {

                $avatar = $tmp_watch->__get('portrait');
                if (!$avatar || $avatar=='')
                    $avatar = '';
                else
                    $avatar = 'images/avatars/'.$avatar;

                $recipe_count = 0;
                $following_count = 0;
                $followed_count = 0;
                $user_info = $tmp_watch->__get('user_info');
                if ($user_info) {
                    $recipe_count = $user_info->__get('recipe_count');
                    $following_count = $user_info->__get('following_count');
                    $followed_count = $user_info->__get('followed_count');
                }

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                    'recipe_count' => $recipe_count,
                    'following_count' => $following_count,
                    'followed_count' => $followed_count,
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 每次检测user info中的following数目
    public function ResetUserInfoAllMyFollowing($follow_count)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            if ($user_info->__get('following_count') != $follow_count) {
                $user_info->__set('following_count', $follow_count);
                $this->entityManager->persist($user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 获取我关注的人数
    public function  getAllMyWatchCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        $this->ResetUserInfoAllMyFollowing($count);

        return $count;
    }

    // 每次检测user info中的following数目，和对方的followed的数目
    public function AddUserInfoAllMyFollowing($other_user_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            $coll_count = $user_info->__get('following_count') + 1;
            $user_info->__set('following_count', $coll_count);
            $this->entityManager->persist($user_info);
            $this->entityManager->flush();
        }

        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $other_user = $user_repository->findOneBy(array('user_id' => $other_user_id));
        if ($other_user) {
            $this->addCredit($other_user_id, GCFlag::Credit_Normal);
            $other_user_info = $other_user->__get('user_info');
            if ($other_user_info) {
                $other_count = $other_user_info->__get('followed_count') + 1;
                $other_user_info->__set('followed_count', $other_count);
                $this->entityManager->persist($other_user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 关注
    public function addMyWatch($watch_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'target_id' => $watch_id));
        if ($tmp_record)
            return GCFlag::GC_AlreadyWatchUser;

        //查找是否有该用户
        $tmp_user = $user_repository->findOneBy(array('user_id' => $watch_id));
        if ($tmp_user)
        {
            $user_relation = new UserRelation();
            $user_relation->__set('user_id', $user_id);
            $user_relation->__set('target_id', $watch_id);
            $this->entityManager->persist($user_relation);
            $this->entityManager->flush();

            //检测
            $this->AddUserInfoAllMyFollowing($watch_id);

            return GCFlag::GC_NoErrorCode;
        } else {
            return GCFlag::GC_AccountNotExist;
        }
    }

    public function isMyWatch($watch_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'target_id' => $watch_id));
        if ($tmp_record)
            return GCFlag::E_IsMyWatch;

        return GCFlag::E_NotMyWatch;
    }

    // 每次检测user info中的following数目
    public function RemoveUserInfoAllMyFollowing($other_user_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            $follow_count = $user_info->__get('following_count') - 1;
            if ($follow_count < 0) {
                $follow_count = 0;
            }
            $user_info->__set('following_count', $follow_count);
            $this->entityManager->persist($user_info);
            $this->entityManager->flush();
        }

        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $other_user = $user_repository->findOneBy(array('user_id' => $other_user_id));
        if ($other_user) {
            $this->removeCredit($other_user_id, GCFlag::Credit_Normal);
            $other_user_info = $other_user->__get('user_info');
            if ($other_user_info) {
                $other_count = $other_user_info->__get('followed_count') - 1;
                if ($other_count < 0) {
                    $other_count = 0;
                }
                $other_user_info->__set('followed_count', $other_count);
                $this->entityManager->persist($other_user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 取消关注
    public function delMyWatch($watchid)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $relation_object = $repository->findOneBy(array('target_id' => $watchid, 'user_id' => $user_id));

        if ($relation_object)
        {
            $this->entityManager->remove($relation_object);
            $this->entityManager->flush();

            $this->RemoveUserInfoAllMyFollowing($watchid);

            return GCFlag::GC_NoErrorCode;
        }

        return GCFlag::GC_NotMyWatchUser;
    }


    // 我的粉丝
    public function getMyFans($limit, $offset=0)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        $result_watches = array();

        $watch_id_s = $repository->findBy(array('target_id' => $user_id), array('id' => 'DESC'), $limit, $offset);

        foreach ($watch_id_s as $watch_id){
            $tmp_id = $watch_id->__get('user_id');
            $tmp_watch = $user_repository->findOneBy(array('user_id' => $tmp_id));
            if ($tmp_watch) {

                $avatar = $tmp_watch->__get('portrait');
                if (!$avatar || $avatar=='')
                    $avatar = '';
                else
                    $avatar = 'images/avatars/'.$avatar;

                $recipe_count = 0;
                $followed_count = 0;
                $following_count = 0;
                $user_info = $tmp_watch->__get('user_info');
                if ($user_info) {
                    $recipe_count = $user_info->__get('recipe_count');
                    $following_count = $user_info->__get('following_count');
                    $followed_count = $user_info->__get('followed_count');
                }

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                    'recipe_count' => $recipe_count,
                    'followed_count' => $followed_count,
                    'following_count' => $following_count
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 每次检测user info中的followed数目
    public function ResetUserInfoAllMyFollowed($follow_count)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_info = $authService->getIdentity()->__get('user_info');
        if ($user_info) {
            if ($user_info->__get('followed_count') != $follow_count) {
                $user_info->__set('followed_count', $follow_count);
                $this->entityManager->persist($user_info);
                $this->entityManager->flush();
            }
        }
    }

    // 获取我粉丝的数目
    public function  getAllMyFansCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.target_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        $this->ResetUserInfoAllMyFollowed($count);

        return $count;
    }

    // 某人关注的人
    public function getUserWatch($user_id, $limit, $offset=0)
    {
        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        $result_watches = array();

        $watch_id_s = $repository->findBy(array('user_id' => $user_id), array('id' => 'DESC'), $limit, $offset);

        foreach ($watch_id_s as $watch_id){
            $tmp_id = $watch_id->__get('target_id');
            $tmp_watch = $user_repository->findOneBy(array('user_id' => $tmp_id));
            if ($tmp_watch) {

                $avatar = $tmp_watch->__get('portrait');
                if (!$avatar || $avatar=='')
                    $avatar = '';
                else
                    $avatar = 'images/avatars/'.$avatar;

                $recipe_count = 0;
                $following_count = 0;
                $followed_count = 0;
                $user_info = $tmp_watch->__get('user_info');
                if ($user_info) {
                    $recipe_count = $user_info->__get('recipe_count');
                    $following_count = $user_info->__get('following_count');
                    $followed_count = $user_info->__get('followed_count');
                }

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                    'recipe_count' => $recipe_count,
                    'following_count' => $following_count,
                    'followed_count' => $followed_count,
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 每次检测user info中的following数目
    public function ResetUserInfoUserFollowing($user_id, $follow_count)
    {
        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $user = $user_repository->findOneBy(array('user_id' => $user_id));
        if ($user) {
            $user_info = $user->__get('user_info');
            if ($user_info) {
                if ($user_info->__get('following_count') != $follow_count) {
                    $user_info->__set('following_count', $follow_count);
                    $this->entityManager->persist($user_info);
                    $this->entityManager->flush();
                }
            }
        }
    }

    // 获取某人关注的人数
    public function  getUserWatchCount($user_id)
    {
        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        $this->ResetUserInfoUserFollowing($user_id, $count);

        return $count;
    }

    // 某人的粉丝
    public function getUserFans($user_id, $limit, $offset=0)
    {
        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        $result_watches = array();

        $watch_id_s = $repository->findBy(array('target_id' => $user_id), array('id' => 'DESC'), $limit, $offset);

        foreach ($watch_id_s as $watch_id){
            $tmp_id = $watch_id->__get('user_id');
            $tmp_watch = $user_repository->findOneBy(array('user_id' => $tmp_id));
            if ($tmp_watch) {

                $avatar = $tmp_watch->__get('portrait');
                if (!$avatar || $avatar=='')
                    $avatar = '';
                else
                    $avatar = 'images/avatars/'.$avatar;

                $recipe_count = 0;
                $followed_count = 0;
                $following_count = 0;
                $user_info = $tmp_watch->__get('user_info');
                if ($user_info) {
                    $recipe_count = $user_info->__get('recipe_count');
                    $followed_count = $user_info->__get('followed_count');
                    $following_count = $user_info->__get('following_count');
                }

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                    'recipe_count' => $recipe_count,
                    'followed_count' => $followed_count,
                    'following_count' => $following_count,
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 每次检测user info中的followed数目
    public function ResetUserInfoUserFollowed($user_id, $follow_count)
    {
        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $user = $user_repository->findOneBy(array('user_id' => $user_id));
        if ($user) {
            $user_info = $user->__get('user_info');
            if ($user_info) {
                if ($user_info->__get('followed_count') != $follow_count) {
                    $user_info->__set('followed_count', $follow_count);
                    $this->entityManager->persist($user_info);
                    $this->entityManager->flush();
                }
            }
        }
    }

    // 某人粉丝的数目
    public function  getUserFansCount($user_id)
    {
        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.target_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        $this->ResetUserInfoUserFollowed($user_id, $count);

        return $count;
    }

    /**************************************************************
     *
     * 加积分
     * @access public
     *
     *************************************************************/
    public function addCredit($user_id, $count)
    {
        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $user = $user_repository->findOneBy(array('user_id' => $user_id));
        if ($user) {
            $credit = $user->__get('credit');
            $credit += $count;
            $user->__set('credit', $credit);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**************************************************************
     *
     * 减积分
     * @access public
     *
     *************************************************************/
    public function removeCredit($user_id, $count)
    {
        $user_repository = $this->entityManager->getRepository('User\Entity\User');
        $user = $user_repository->findOneBy(array('user_id' => $user_id));
        if ($user) {
            $credit = $user->__get('credit');
            $credit -= $count;
            if ($credit < 0) {
                $credit = 0;
            }
            $user->__set('credit', $credit);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**************************************************************
     *
     * 查询M6商品
     * @access public
     *
     *************************************************************/
    public function QueryWaresFromM6($keyword, $limit, $page)
    {
        $search_info = '{"Keyword":"'. $keyword .'","PageIndex":' . (string)($page - 1) . ',"PageRows":'. (string)$limit . '}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::SEARCH_CMD;
        $post_array['Data'] = addslashes($search_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::SEARCH_CMD, $search_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $data_json = json_decode($res_json['Data'], true);

                $page_index = $data_json['PageIndex'] + 1;
                $page_rows = $data_json['PageRows'];
                $total_count = $data_json['TotalCount'];
                $row_array = array();

                //如果和传过去的page不同的话，那么返回0个
                if ($page_index < $page) {
                    $page_index = $page;
                }
                else {
                    foreach ($data_json['Rows'] as $res_row) {
                        $row = array();
                        $row['id'] = intval($res_row['Id']);
                        $row['name'] = $res_row['Name'];
                        $row['code'] = $res_row['Code'];
                        $row['remark'] = $res_row['Remark'];
                        $row['norm'] = $res_row['Norm'];
                        $row['unit'] = $res_row['Unit'];
                        $row['price'] = $res_row['Price'];
                        $row['image_url'] = $res_row['ImageUrl'];
                        $row['deal_method'] = $res_row['DealMethod'];

                        array_push($row_array,$row);
                    }
                }

                $ware_array = array();
                $ware_array['page'] = $page_index;
                $ware_array['total_count'] = $total_count;
                $ware_array['wares'] = $row_array;

                //返回成功
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result,$error_code,$ware_array);

            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Product_Invalid){
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_ProductInvalid;
                return array($result,$error_code);
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result,$error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }

    /**************************************************************
     *
     * 订购M6商品
     * @access public
     *
     *************************************************************/
    public function orderWares($wares_str) {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $msix_id = $authService->getIdentity()->__get('msix_id');

        $order_info = '{"CustId":'. (string)$msix_id .',' . $wares_str .'}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::ORDER_CMD;
        $post_array['Data'] = addslashes($order_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::ORDER_CMD, $order_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $order_id = $res_json['Data'];

                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result,$error_code,$order_id);
            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Order_ActInvalid){
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_OrderAccountInvalid;
                return array($result,$error_code);
            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Order_Invalid) {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_OrderAccountInvalid;
                return array($result,$error_code);
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result,$error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result,$error_code);
        }
    }

    /**************************************************************
     *
     * 查询历史订单
     * @access public
     *
     *************************************************************/
    public function QueryHistoryOrders($start_day, $end_day, $limit, $page)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $msix_id = $authService->getIdentity()->__get('msix_id');

        $search_info = '{"CustId":'. (string)$msix_id .',"StartDay":"' . $start_day . '","EndDay":"' .
            $end_day . '","PageIndex":' . (string)($page - 1) . ',"PageRows":'. (string)$limit . '}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::HIS_ORDERS_CMD;
        $post_array['Data'] = addslashes($search_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::HIS_ORDERS_CMD, $search_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 20,
        ));

        try {
            $reg_response = $reg_client->dispatch($reg_request);
            if ($reg_response->isSuccess()) {
                //$this->logger->info($reg_response->getBody());
                $res_content = $reg_response->getBody();

                $res_json = json_decode($res_content, true); // convert into array

                if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                    $data_json = json_decode($res_json['Data'], true);

                    $page_index = $data_json['PageIndex'] + 1;
                    $page_rows = $data_json['PageRows'];
                    $total_count = $data_json['TotalCount'];
                    $row_array = array();

                    //如果和传过去的page不同的话，那么返回0个
                    if ($page_index < $page) {
                        $page_index = $page;
                    } else {
                        foreach ($data_json['Rows'] as $res_row) {
                            $row = array();
                            $row['id'] = intval($res_row['Id']);
                            $row['cust_name'] = $res_row['CustName'];
                            $row['code'] = $res_row['Code'];
                            $row['delivery_type'] = $res_row['DeliveryType'];
                            $row['delivery_time_type'] = $res_row['DeliveryTimeType'];
                            $row['recv_mobile'] = $res_row['RecvMobile'];
                            $row['cost'] = $res_row['Cost'];
                            $row['create_time'] = $res_row['CreateTime'];

                            $row['order_wares'] = array();
                            foreach ($res_row['OrderWares'] as $ware_item) {
                                $order_ware = array();
                                $order_ware['id'] = intval($ware_item['Id']);
                                $order_ware['name'] = $ware_item['Name'];
                                $order_ware['code'] = $ware_item['Code'];
                                $order_ware['remark'] = $ware_item['Remark'];
                                $order_ware['norm'] = $ware_item['Norm'];
                                $order_ware['unit'] = $ware_item['Unit'];
                                $order_ware['price'] = $ware_item['Price'];
                                $order_ware['image_url'] = $ware_item['ImageUrl'];
                                $order_ware['deal_method'] = $ware_item['DealMethod'];
                                $order_ware['quantity'] = $ware_item['Quantity'];
                                $order_ware['cost'] = $ware_item['Cost'];
                                array_push($row['order_wares'],$order_ware);
                            }

                            array_push($row_array,$row);
                        }
                    }

                    $ware_order_array = array();
                    $ware_order_array['page'] = $page_index;
                    $ware_order_array['total_count'] = $total_count;
                    $ware_order_array['orders'] = $row_array;

                    //返回成功
                    $result = GCFlag::GC_Success;
                    $error_code = GCFlag::GC_NoErrorCode;
                    return array($result,$error_code,$ware_order_array);

                } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Product_Invalid){
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_ProductInvalid;
                    return array($result,$error_code);
                } else {
                    $result = GCFlag::GC_Failed;
                    $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                    return array($result,$error_code);
                }

            } else {
                // 甲方服务器4XX，5XX
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerConnError;
                return array($result, $error_code);
            }
        } catch (RuntimeException $e) {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }

    /**************************************************************
     *
     * 查询当天购买记录
     * @access public
     *
     *************************************************************/
    public function QueryDaySales($test_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');

		if (!$authService->getIdentity()) {
     	   $result = GCFlag::GC_Failed;
           $error_code = GCFlag::GC_AuthAccountInvalid;
     	   return array($result, $error_code);
		}
        
        $msix_id = $authService->getIdentity()->__get('msix_id');
        if ($test_id != 0) {
            $msix_id = $test_id;
        }

        $query_info = (string)$msix_id;
        $post_array = array();
        $post_array['Cmd'] = CommonDef::DAY_SALES_CMD;
        $post_array['Data'] = $query_info;
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::DAY_SALES_CMD, $query_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $data_json = json_decode($res_json['Data'], true);

                $result_array = array();
                $result_array['time'] = $data_json['Time'];
                $result_array['sale_fee'] = $data_json['SaleFee'];
                $result_array['sale_count'] = $data_json['SaleCount'];
                $result_array['condition'] = $data_json['IsMeetConditions']; // 1:qualified; 0:not
                $result_array['remark'] = $data_json['Remark'];

                //返回成功
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result, $error_code, $result_array);

            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result, $error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }


    /**************************************************************
     *
     * 获取优惠券
     * @access public
     *
     *************************************************************/
    public function GetCoupon($coupon_id, $test_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');

		if (!$authService->getIdentity()) {
     	   $result = GCFlag::GC_Failed;
           $error_code = GCFlag::GC_AuthAccountInvalid;
     	   return array($result, $error_code);
		}

        $msix_id = $authService->getIdentity()->__get('msix_id');

        if ($test_id != 0) {
            $msix_id = $test_id;
        }

        $query_info = '{"CustId":'. (string)$msix_id . ',"CouponId":'. (string)$coupon_id .'}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::GET_COUPON_CMD;
        $post_array['Data'] = addslashes($query_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::GET_COUPON_CMD, $query_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $data_json = json_decode($res_json['Data'], true);

                $row_array = array();
                foreach ($data_json['Rows'] as $res_row) {
                    $row = array();
                    $row['time'] = $res_row['Time'];
                    $row['eff_day'] = $res_row['EffDay'];
                    $row['exp_day'] = $res_row['ExpDay'];
                    $row['coupon'] = $res_row['Coupon']; //优惠券编号
                    $row['coupon_id'] = $res_row['CouponId']; //正牌的coupon id
                    $row['coupon_remark'] = $res_row['CouponRemark'];
                    $row['stores'] = $res_row['Stores'];
                    $row['condition'] = $res_row['IsMeetConditions'];
                    $row['remark'] = $res_row['Remark'];
                    $row['is_delay'] = $res_row['IsDelay'];
                    $row['supplier'] = $res_row['supplier'];
                    $row['ktype'] = $res_row['ktype'];
                    $row['status'] = $res_row['status'];
                    $row['name'] = $res_row['name'];
                    $row['url'] = $res_row['url'];
                    $row['img'] = $res_row['img'];
                    $row['cctime'] = $res_row['cctime'];
                    $row['ctime'] = $res_row['ctime'];
                    $row['val'] = $res_row['val'];
                    $row['wid'] = $res_row['wid'];
                    $row['isused'] = $res_row['isused'];

                    array_push($row_array, $row);
                }

                $coupon_array = array();
                $coupon_array['coupons'] = $row_array;

                //返回成功
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result, $error_code, $coupon_array);

            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Fail) {
                //领取失败
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_DelayRecordNotValid;
                return array($result, $error_code);

            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result, $error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }


    /**************************************************************
     *
     * 延期获取优惠券
     * @access public
     *
     *************************************************************/
    public function DelayCoupon($test_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
		if (!$authService->getIdentity()) {
     	   $result = GCFlag::GC_Failed;
           $error_code = GCFlag::GC_AuthAccountInvalid;
     	   return array($result, $error_code);
		}

        $msix_id = $authService->getIdentity()->__get('msix_id');

        if ($test_id != 0) {
            $msix_id = $test_id;
        }
        $query_info = (string)$msix_id;
        $post_array = array();
        $post_array['Cmd'] = CommonDef::DELAY_GET_COUPON_CMD;
        $post_array['Data'] = $query_info;
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::DELAY_GET_COUPON_CMD, $query_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $data_json = json_decode($res_json['Data'], true);

                $result_array = array();
                $result_array['delay_rst'] = GCFlag::E_IsDelayed;
                $result_array['id'] = $data_json['Id'];
                $result_array['time'] = $data_json['Time'];
                $result_array['eff_day'] = $data_json['EffDay'];
                $result_array['exp_day'] = $data_json['ExpDay'];
                $result_array['condition'] = $data_json['IsMeetConditions'];
                $result_array['remark'] = $data_json['Remark'];

                //返回成功
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result, $error_code, $result_array);

            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Fail) {

                $result_array = array();
                $result_array['delay_rst'] = GCFlag::E_NothingDelay;
                $result_array['id'] = "";
                $result_array['time'] = "";
                $result_array['eff_day'] = "";
                $result_array['exp_day'] = "";
                $result_array['condition'] = 0;
                $result_array['remark'] = "";

                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result, $error_code, $result_array);

            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result, $error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
        }
    }


    /**************************************************************
     *
     * 查询我的优惠券
     * @access public
     *
     *************************************************************/
    public function GetMyCoupons($limit, $page, $test_id)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
		if (!$authService->getIdentity()) {
     	   $result = GCFlag::GC_Failed;
           $error_code = GCFlag::GC_AuthAccountInvalid;
     	   return array($result, $error_code);
		}

        $msix_id = $authService->getIdentity()->__get('msix_id');

        if ($test_id != 0) {
            $msix_id = $test_id;
        }

        $search_info = '{"CustId":'. (string)$msix_id . ',"PageIndex":' . (string)($page - 1) . ',"PageRows":'. (string)$limit . '}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::GET_MY_COUPONS_CMD;
        $post_array['Data'] = addslashes($search_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::GET_MY_COUPONS_CMD, $search_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        // 开始向服务器请求数据
        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));
        $reg_request->getPost()->set('Data', $post_str);

        $reg_client = new Client();
        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $reg_client->setOptions(array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            //$this->logger->info($reg_response->getBody());
            $res_content = $reg_response->getBody();

            $res_json = json_decode($res_content, true); // convert into array

            if (intval($res_json['Flag']) == M6Flag::M6FLAG_Success) {

                $data_json = json_decode($res_json['Data'], true);

                $page_index = $data_json['PageIndex'] + 1;
                $page_rows = $data_json['PageRows'];
                $total_count = $data_json['TotalCount'];
                $row_array = array();

                //如果和传过去的page不同的话，那么返回0个
                if ($page_index < $page) {
                    $page_index = $page;
                } else {
                    foreach ($data_json['Rows'] as $res_row) {
                        $row = array();
                        $row['time'] = $res_row['Time'];
                        $row['eff_day'] = $res_row['EffDay'];
                        $row['exp_day'] = $res_row['ExpDay'];
                        $row['coupon'] = $res_row['Coupon']; //优惠券编号
                        $row['coupon_id'] = $res_row['CouponId']; //正牌的coupon id
                        $row['coupon_remark'] = $res_row['CouponRemark'];
                        $row['stores'] = $res_row['Stores'];
                        $row['condition'] = $res_row['IsMeetConditions'];
                        $row['remark'] = $res_row['Remark'];
                        $row['is_delay'] = $res_row['IsDelay'];
                        $row['supplier'] = $res_row['supplier'];
                        $row['ktype'] = $res_row['ktype'];
                        $row['status'] = $res_row['status'];
                        $row['name'] = $res_row['name'];
                        $row['url'] = $res_row['url'];
                        $row['img'] = $res_row['img'];
                        $row['cctime'] = $res_row['cctime'];
                        $row['ctime'] = $res_row['ctime'];
                        $row['val'] = $res_row['val'];
                        $row['wid'] = $res_row['wid'];
                        $row['isused'] = $res_row['isused'];

                        array_push($row_array, $row);
                    }
                }

                $coupon_array = array();
                $coupon_array['page'] = $page_index;
                $coupon_array['total_count'] = $total_count;
                $coupon_array['coupons'] = $row_array;

                //返回成功
                $result = GCFlag::GC_Success;
                $error_code = GCFlag::GC_NoErrorCode;
                return array($result,$error_code,$coupon_array);

            } else if (intval($res_json['Flag']) == M6Flag::M6FLAG_Product_Invalid){
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_ProductInvalid;
                return array($result,$error_code);
            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_M6ServerError; // M6服务器返回结果
                return array($result,$error_code);
            }

        } else {
            // 甲方服务器4XX，5XX
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_M6ServerConnError;
            return array($result, $error_code);
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**************************************************************
     *
     *	使用特定function对数组中所有元素做处理
     *	@param	array	&$array		        要处理的字符串
     *	@param	string	$function	        要执行的函数
     *	@param	boolean $apply_to_keys_also	是否也应用到key上
     *  @return boolean
     *	@access public
     *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }

        if (is_array($array)){
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

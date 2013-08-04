<?php

/**
 * CookController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use Application\Controller\BaseAbstractActionController;
use Zend\View\Model\JsonModel;

class CookController extends BaseAbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;


    //我的收藏
    public function mycollAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $collect_recipes = $cookService->getMyCollection(10,($page-1)*10);
            $collect_count = intval($cookService->getAllMyCollCount());

            return new JsonModel(array(
                'result' => 0,
                'total' => $collect_count,
                'cur_page' => $page,
                'result_recipes' => $collect_recipes,
            ));
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    //添加收藏
    public function addmycollAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $collid = -1;
            if ($this->params()->fromQuery('collid')&&$this->params()->fromQuery('collid')!='')
            {
                $collid = intval($this->params()->fromQuery('collid'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $result = $cookService->addMyCollection($collid);

            if ($result == 0)
            {
                return new JsonModel(array(
                    'result' => 0,
                    'collid' => $collid,
                ));
            }
            else
            {
                return new JsonModel(array(
                    'result' => $result,
                    'collid' => -1,
                ));
            }
        }

        return new JsonModel(array(
            'result' => 1,
            'collid' => -1,
        ));
    }

    //取消收藏
    public function delmycollAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $collid = -1;
            if ($this->params()->fromQuery('collid')&&$this->params()->fromQuery('collid')!='')
            {
                $collid = intval($this->params()->fromQuery('collid'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $result = $cookService->delMyCollection($collid);
            if ($result == 0)
            {
                return new JsonModel(array(
                    'result' => 0,
                    'collid' => $collid,
                ));
            }
        }

        return new JsonModel(array(
            'result' => 1,
            'collid' => -1,
        ));
    }

    //查询用户信息
    public function kitchenAction()
    {
        $result = 1;
        $errorcode = 0;

        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $user_repository = $this->getEntityManager()->getRepository('User\Entity\User');

        if($this->isMobile($request)) {

            $user_id = -1;
            if ($this->params()->fromQuery('userid')&&$this->params()->fromQuery('userid')!='')
            {
                $user_id = intval($this->params()->fromQuery('userid'));
            }

            $user = $user_repository->findOneBy(array('user_id' => $user_id));
            if ($user)
            {
                $user_id = $user->__get('user_id');
                $nickname = $user->__get('display_name');
                $portrait = $user->__get('portrait');
                if (!$portrait || $portrait=='')
                    $portrait = '';
                else
                    $portrait = 'images/avatars/'.$portrait;

                $gender = $user->__get('gender');
                $city = $user->__get('city');
                $intro = $user->__get('intro');

                $cookService = $this->getServiceLocator()->get('cook_service');
                $result_array = $cookService->getUserRecipes($user_id,3,0);


                $watch = 1;
                if ($authService->hasIdentity())
                    $watch = $cookService->isMyWatch($user_id);

                $result_info = array(
                    'userid' => $user_id,
                    'nickname' => $nickname,
                    'avatar' => $portrait,
                    'gender' => $gender,
                    'city' => $city,
                    'intro' => $intro,
                    'totalrecipecount' => intval($result_array[0]),
                    'recipes' => $result_array[1],
                    'watch' => $watch
                );

                $result = 0;

                return new JsonModel(array(
                    'result' => $result,
                    'result_kitchen_info' => $result_info,
                ));
            }
        }

        return new JsonModel(array(
            'result' => $result,
            'errorcode' => $errorcode,
        ));
    }

    //我的关注
    public function mywatchAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $mywatchusers = $cookService->getMyWatch(10,($page-1)*10);
            $watch_count = intval($cookService->getAllMyWatchCount());

            return new JsonModel(array(
                'result' => 0,
                'total' => $watch_count,
                'cur_page' => $page,
                'result_users' => $mywatchusers,
            ));
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    //添加关注
    public function watchAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $watch_id = -1;
            if ($this->params()->fromQuery('watchid')&&$this->params()->fromQuery('watchid')!='')
            {
                $watch_id = intval($this->params()->fromQuery('watchid'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $result = $cookService->addMyWatch($watch_id);

            if ($result == 0)
            {
                return new JsonModel(array(
                    'result' => 0,
                    'watchid' => $watch_id,
                ));
            }
            else
            {
                return new JsonModel(array(
                    'result' => $result,
                    'collid' => -1,
                ));
            }
        }

        return new JsonModel(array(
            'result' => 1,
            'collid' => -1,
        ));
    }

    //取消关注
    public function unwatchAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $watchid = -1;
            if ($this->params()->fromQuery('watchid') && $this->params()->fromQuery('watchid')!='')
            {
                $watchid = intval($this->params()->fromQuery('watchid'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $result = $cookService->delMyWatch($watchid);
            if ($result == 0)
            {
                return new JsonModel(array(
                    'result' => 0,
                    'watchid' => $watchid,
                ));
            }
        }

        return new JsonModel(array(
            'result' => 1,
            'watchid' => -1,
        ));
    }

    //我的粉丝
    public function myfansAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $myfans = $cookService->getMyFans(10,($page-1)*10);
            $fans_count = intval($cookService->getAllMyFansCount());

            return new JsonModel(array(
                'result' => 0,
                'total' => $fans_count,
                'cur_page' => $page,
                'result_users' => $myfans,
            ));
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    //我的菜谱
    public function myrecipesAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request) && $authService->hasIdentity())
        {
            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $collect_recipes = $cookService->getMyRecipes(10,($page-1)*10);
            $recipes_count = intval($cookService->getAllMyRecipesCount());

            return new JsonModel(array(
                'result' => 0,
                'total' => $recipes_count,
                'cur_page' => $page,
                'result_recipes' => $collect_recipes,
            ));
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }

    //某人的菜谱
    public function usersrecipesAction()
    {
        $request = $this->getRequest();

        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($this->isMobile($request))
        {
            $user_id = -1;
            if ($this->params()->fromQuery('userid')&&$this->params()->fromQuery('userid')!='')
            {
                $user_id = intval($this->params()->fromQuery('userid'));
            }

            $page = 1;
            if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
            {
                $page = intval($this->params()->fromQuery('page'));
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $result_recipes = $cookService->getUserRecipes($user_id, 10,($page-1)*10);

            return new JsonModel(array(
                'result' => 0,
                'totalrecipecount' => $result_recipes[0],
                'result_recipes' => $result_recipes[1],
            ));
        }

        return new JsonModel(array(
            'result' => 1,
        ));
    }


    //收藏，粉丝数量，关注数量

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

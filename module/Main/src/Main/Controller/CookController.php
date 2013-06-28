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

            return new JsonModel(array(
                'result' => 0,
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

            return new JsonModel(array(
                'result' => 0,
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

            return new JsonModel(array(
                'result' => 0,
                'result_recipes' => $collect_recipes,
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

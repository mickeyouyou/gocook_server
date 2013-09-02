<?php

/**
 * CookController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use App\Lib\GCFlag;
use App\Controller\BaseAbstractActionController;
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
                if ($page < 1)
                    $page = 1;
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $collect_recipes = $cookService->getMyCollection(10,($page-1)*10);
            $collect_count = intval($cookService->getAllMyCollCount());

            return new JsonModel(array(
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'total' => $collect_count,
                'cur_page' => $page,
                'result_recipes' => $collect_recipes,
            ));
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
            ));
        }
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
                if ($collid < 1)
                    $collid = 1;
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $error_result = $cookService->addMyCollection($collid);

            if ($error_result == GCFlag::GC_NoErrorCode)
            {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => $error_result,
                    'collid' => $collid,
                ));
            }
            else
            {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => $error_result,
                    'collid' => -1,
                ));
            }
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
                'collid' => -1,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
                'collid' => -1,
            ));
        }
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
                if ($collid < 1)
                    $collid = 1;
            }

            $cookService = $this->getServiceLocator()->get('cook_service');
            $code_result = $cookService->delMyCollection($collid);
            if ($code_result == GCFlag::GC_NoErrorCode) {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => $code_result,
                    'collid' => $collid,
                ));
            } else {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => $code_result,
                    'collid' => -1,
                ));
            }
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
                'collid' => -1,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
                'collid' => -1,
            ));
        }
    }

    //查询用户信息
    public function kitchenAction()
    {
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

                $watch = GCFlag::E_NotMyWatch;
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

                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => GCFlag::GC_NoErrorCode,
                    'result_kitchen_info' => $result_info,
                ));
            } else {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => GCFlag::GC_AccountNotExist,
                ));
            }
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
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
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'total' => $watch_count,
                'cur_page' => $page,
                'result_users' => $mywatchusers,
            ));
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
            ));
        }
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

            if ($result == GCFlag::GC_NoErrorCode)
            {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => $result,
                    'watchid' => $watch_id,
                ));
            }
            else
            {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => $result,
                    'watchid' => -1,
                ));
            }
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
                'watchid' => -1,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
                'watchid' => -1,
            ));
        }
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
            if ($result == GCFlag::GC_NoErrorCode)
            {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => $result,
                    'watchid' => $watchid,
                ));
            } else {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => $result,
                    'watchid' => -1,
                ));
            }

        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
                'watchid' => -1,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
                'watchid' => -1,
            ));
        }
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
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'total' => $fans_count,
                'cur_page' => $page,
                'result_users' => $myfans,
            ));
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
            ));
        }
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
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'total' => $recipes_count,
                'cur_page' => $page,
                'result_recipes' => $collect_recipes,
            ));
        } else if (!$this->isMobile($request)){
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_AuthAccountInvalid,
            ));
        }
    }

    //某人的菜谱
    public function usersrecipesAction()
    {
        $request = $this->getRequest();

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
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'totalrecipecount' => $result_recipes[0],
                'result_recipes' => $result_recipes[1],
            ));
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
    }


    //收藏，粉丝数量，关注数量



    /**************************************************************
     *
     * 查询M6商品
     * url: cook/search_wares
     * @get keyword
     * @access public
     *
     *************************************************************/
    public function searchWaresAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $wares = array();

        $request = $this->getRequest();

        if($this->isMobile($request)) {

            $keyword = '';
            if ($this->params()->fromQuery('keyword') && trim($this->params()->fromQuery('keyword')) != '')
            {
                $keyword = trim($this->params()->fromQuery('keyword'));

                $page = 1;
                if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
                {
                    $page = intval($this->params()->fromQuery('page'));
                    if ($page < 1)
                        $page = 1;
                }

                $cookService = $this->getServiceLocator()->get('cook_service');
                $query_result = $cookService->QueryWaresFromM6($keyword, 10, $page);

                $result = $query_result[0];
                $error_code = $query_result[1];

                if ($result == GCFlag::GC_Success){
                    // 取得商品数据
                    $wares = $query_result[2];
                }

            } else {
                $result = GCFlag::GC_Failed;
                $error_code = GCFlag::GC_KeywordNull;
            }
        } else {
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        if ($result == GCFlag::GC_Success) {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'wares' => $wares,
            ));
        } else {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
            ));
        }
    }

    /**************************************************************
     *
     * 订购M6商品
     * url: cook/order
     * @post wares
     * @access public
     *
     *************************************************************/
    public function orderAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $order_id = 0;

        $request = $this->getRequest();
        if($this->isMobile($request)) {
            if ($request->isPost()) {
                $data = $request->getPost();
                if($this->params()->fromPost('wares') && $this->params()->fromPost('wares') != '') {
                    $cookService = $this->getServiceLocator()->get('cook_service');
                    $order_result = $cookService->orderWares($data['wares']);
                    $result = $order_result[0];
                    $error_code = $order_result[1];

                    if ($result == GCFlag::GC_Success){
                        $order_id = $order_result[2];
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
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        if ($result == GCFlag::GC_Success) {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'order_id' => $order_id,
            ));
        } else {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
            ));
        }
    }

    /**************************************************************
     *
     * 查询M6历史订单
     * url: cook/his_orders
     * @get start_day end_day page
     * @access public
     *
     *************************************************************/
    public function hisOrdersAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;
        $his_orders = array();
        $request = $this->getRequest();
        if($this->isMobile($request)) {
            if ($request->isPost()) {
                $data = $request->getPost();
                $start_day = '';
                $end_day = '';
                if ($this->params()->fromPost('start_day') && trim($this->params()->fromPost('start_day')) != ''
                    && $this->params()->fromPost('end_day') && $this->params()->fromPost('end_day')){

                    $start_day = trim($this->params()->fromPost('start_day'));
                    $end_day = trim($this->params()->fromPost('start_day'));

                    $page = 1;
                    if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
                    {
                        $page = intval($this->params()->fromQuery('page'));
                        if ($page < 1)
                            $page = 1;
                    }

                    $cookService = $this->getServiceLocator()->get('cook_service');
                    $query_result = $cookService->QueryHistoryOrders($start_day, $end_day, 10, $page);

                    $result = $query_result[0];
                    $error_code = $query_result[1];

                    if ($result == GCFlag::GC_Success){
                        // 取得数据
                        $his_orders = $query_result[2];
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
            $result = GCFlag::GC_Failed;
            $error_code = GCFlag::GC_NoMobileDevice;
        }

        if ($result == GCFlag::GC_Success) {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
                'wares' => $his_orders,
            ));
        } else {
            return new JsonModel(array(
                'result' => $result,
                'errorcode' => $error_code,
            ));
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
}

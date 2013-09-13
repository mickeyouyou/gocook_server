<?php

namespace Main\Service;

use App\Lib\Common;
use App\Lib\CommonDef;
use App\Lib\M6Flag;
use App\Lib\GCFlag;
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
                    'dish_count' => $tmp_recipe->__get('dish_count')
                );

                array_push($result_recipes, $result_recipe);
            }
        }
        return $result_recipes;
    }

    // 获取我收藏的菜谱数
    public function  getAllMyCollCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserCollection u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        return $count;
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

    // 删除收藏菜谱
    public function delMyCollection($collid)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserCollection');
        $relation_object = $repository->findOneBy(array('recipe_id' => $collid, 'user_id' => $user_id));

        if ($relation_object)
        {
            $this->entityManager->remove($relation_object);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        }
        return GCFlag::GC_NotMyCollectRecipe;
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
                'image' => 'images/recipe/140/'.$recipe->__get('cover_img'),
                'dish_count' => $recipe->__get('dish_count')
            );

            array_push($result_recipes, $result_recipe);
        }
        return $result_recipes;
    }

    public function getAllMyRecipesCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.recipe_id) FROM Main\Entity\Recipe u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    // 获取某人的菜谱
    public function getUserRecipes($userid, $limit, $offset=0)
    {
        $recipe_repository = $this->entityManager->getRepository('Main\Entity\Recipe');

        $result_recipes = array();

        $recipes = $recipe_repository->findBy(array('user_id' => $userid), null, $limit, $offset);

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

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\Recipe u WHERE u.user_id=?1');
        $query->setParameter(1, $userid);
        $count = $query->getSingleScalarResult();

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

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 获取我关注的人数
    public function  getAllMyWatchCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.user_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    // 关注
    public function addMyWatch($watchid)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');
        $user_repository = $this->entityManager->getRepository('User\Entity\User');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'target_id' => $watchid));
        if ($tmp_record)
            return GCFlag::GC_AlreadyWatchUser;

        //查找是否有该用户
        $tmp_user = $user_repository->findOneBy(array('user_id' => $watchid));
        if ($tmp_user)
        {
            $user_relation = new UserRelation();
            $user_relation->__set('user_id', $user_id);
            $user_relation->__set('target_id', $watchid);
            $this->entityManager->persist($user_relation);
            $this->entityManager->flush();

            return GCFlag::GC_NoErrorCode;
        } else {
            return GCFlag::GC_AccountNotExist;
        }
    }

    public function isMyWatch($watchid)
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $repository = $this->entityManager->getRepository('Main\Entity\UserRelation');

        //查找是否有该记录
        $tmp_record = $repository->findOneBy(array('user_id' => $user_id, 'target_id' => $watchid));
        if ($tmp_record)
            return GCFlag::E_IsMyWatch;

        return GCFlag::E_NotMyWatch;
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

                $result_watch = array(
                    'user_id' => $tmp_watch->__get('user_id'),
                    'name' => $tmp_watch->__get('display_name'),
                    'portrait' => $avatar,
                );

                array_push($result_watches, $result_watch);
            }
        }
        return $result_watches;
    }

    // 获取我粉丝的数目
    public function  getAllMyFansCount()
    {
        $authService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        $user_id = $authService->getIdentity()->__get('user_id');

        $query = $this->entityManager->createQuery('SELECT COUNT(u.user_id) FROM Main\Entity\UserRelation u WHERE u.target_id=?1');
        $query->setParameter(1, $user_id);
        $count = $query->getSingleScalarResult();

        return $count;
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
            $this->logger->info($reg_response->getBody());
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
            $this->logger->info($reg_response->getBody());
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
            'timeout'      => 30
        ));

        $reg_response = $reg_client->dispatch($reg_request);

        if ($reg_response->isSuccess()) {
            $this->logger->info($reg_response->getBody());
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
                        foreach ($data_json['OrderWares'] as $ware_item) {
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

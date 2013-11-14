<?php

/**
 * Main IndexController
 * @copyright Copyright (c) 2005-2012 BadPanda Inc.
 */

namespace Main\Controller;

use App\Controller\BaseAbstractActionController;
use App\Lib\CommonDef;
use App\Lib\GCFlag;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use App\Lib\Common;
use Zend\Http\Request;
use Zend\Http\Client;
use App\Lib\Cryptogram;

class IndexController extends BaseAbstractActionController {

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postDispatch'), -100);
    }

    public function postDispatch()
    {

    }
  
    public function indexAction() {

        $bad_json = "{ 'bar': 'baz' }";

        $bad_json = '{ bar: "baz" }';

        $bad_json = '{ "bar": "baz", }';

        $aaa = json_decode($bad_json);

        var_dump($aaa);



        $result = new JsonModel(array(
	        'some_parameter' => 'some value',
            'success'=>true,
        ));

        $reg_request = new Request();
        $reg_request->setUri(CommonDef::M6SERVER);
        $reg_request->setMethod('POST');
        $reg_request->getHeaders()->addHeaders(array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'));

        $account = '15000021035';
        $token = 'rmHSyDSm1Dk=';
        $login_info = '{"Account":"'. $account .'","Password":"' . $token . '"}';


        $key = md5('DAB578EC-6C01-4180-939A-37E6BE8A81AF', true);
        $real_key = $key . "\0\0\0\0\0\0\0\0";

        $iv = md5('117A5C0F', true);
        $real_iv = "\0\0\0\0\0\0\0\0";
        for ($i = 0; $i < 8; $i++)
        {
            $real_iv[$i] = chr(abs(ord($iv[$i]) - ord($iv[$i+1])));
        }

        $content = base64_decode('rmHSyDSm1Dk=');
        $aaa = Cryptogram::decryptByTDES($content, $real_key, $real_iv);
        echo $aaa;




//        // 注册
//        $post_array = array();
//        $post_array['Cmd'] = CommonDef::REGISTER_CMD;
//        $post_array['Data'] = addslashes($login_info);
//        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::REGISTER_CMD, $login_info);

//        // 登陆
//        $post_array = array();
//        $post_array['Cmd'] = Common::AUTH_CMD;
//        $post_array['Data'] = addslashes($login_info);
//        $post_array['Md5'] = Common::EncryptAppReqData(Common::AUTH_CMD, $login_info);

//
        // 搜索
        $search_info = '{"Keyword":"'. '牛肉' .'","PageIndex":' . (string)0 . ',"PageRows":'. (string)10 . '}';
        $post_array = array();
        $post_array['Cmd'] = CommonDef::SEARCH_CMD;
        $post_array['Data'] = addslashes($search_info);
        $post_array['Md5'] = Common::EncryptAppReqData(CommonDef::SEARCH_CMD, $search_info);
//
//        // 下单
//        $search_info = '{"CustId":'. '17' .',"Wares":[{"WareId":6745,"Quantity":1,"Remark":"好，ok"}]}';
//        $post_array = array();
//        $post_array['Cmd'] = Common::ORDER_CMD;
//        $post_array['Data'] = addslashes($search_info);
//        $post_array['Md5'] = Common::EncryptAppReqData(Common::ORDER_CMD, $search_info);

//
//        // 查询历史订单
//        $search_info = '{"CustId":'. '17' .',"StartDay":"2013-03-02","EndDay":"2013-09-10","PageIndex":1,"PageRows":10}';
//        $post_array = array();
//        $post_array['Cmd'] = Common::HIS_ORDERS_CMD;
//        $post_array['Data'] = addslashes($search_info);
//        $post_array['Md5'] = Common::EncryptAppReqData(Common::HIS_ORDERS_CMD, $search_info);

        $this->arrayRecursive($post_array, 'urlencode', false);
        $post_str = urldecode(json_encode($post_array));//not use Json::encode because of escape

        echo $post_str . "\n\n";
//        //$request_post_data = '{' . '"Cmd":' . (string)Common::HIS_ORDERS_CMD . ',"Data":"' . '{\"CustId\":19,\"StartDay\":\"2013-03-12\",\"EndDay\":\"2013-09-11\",\"PageIndex\":1,\"PageRows\":10}' . '","Md5":"' . 'x5ybbJQmrRAuV7bTMCUHZw==' . '"}';
//
//       // echo $post_str;
//
//        $reg_request->getPost()->set('Data', $post_str);
//
//        //var_dump($reg_request->getPost());
//
//        $reg_client = new Client();
//        $reg_client->setAdapter('Zend\Http\Client\Adapter\Curl');
//
//        $reg_client->setOptions(array(
//            'maxredirects' => 0,
//            'timeout'      => 30
//        ));
//        $reg_response = $reg_client->send($reg_request);
//
//        if ($reg_response->isSuccess()) {
//            var_dump ($reg_response->getBody());
//
//            $res_content = $reg_response->getBody();
//            $res_json = json_decode($res_content);
//
//            var_dump($res_json);
//        }

        return $result;
    }

    public function iosMainAction() {

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $topnew_img = 'images/recipe/140/265058.1.jpg';

            $recipeService = $this->getServiceLocator()->get('recipe_service');
            $topRecipe = $recipeService->getTopCollectedRecipe();
            $tophot_img = 'images/recipe/140/'.$topRecipe->cover_img;

            $recommend_items = array();
            $recommend_keywords = array('家常菜','猪肉','快手菜','汤羹','鱼','夏日菜');
            foreach ($recommend_keywords as $keyword){
                $recipes = $recipeService->getRecipesByKeywordOfCatgory($keyword, 4, 0);
                $recommend_item = array();
                $recommend_item['name'] = $keyword;
                $recommend_item['images'] = array();
                foreach ($recipes as $recipe){
                    array_push($recommend_item['images'], 'images/recipe/140/'.$recipe->__get('cover_img'));
                }
                array_push($recommend_items, $recommend_item);
            }
            return new JsonModel(array(
                'result' => GCFlag::GC_Success,
                'errorcode' => GCFlag::GC_NoErrorCode,
                'topnew_img' => $topnew_img,
                'tophot_img' => $tophot_img,
                'recommend_items' => $recommend_items,
            ));

        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
    }

    public function searchAction()
    {
        $result = GCFlag::GC_Success;
        $error_code = GCFlag::GC_NoErrorCode;

        $request = $this->getRequest();

        if ($this->isMobile($request))
        {
            $recipeService = $this->getServiceLocator()->get('recipe_service');
            if ($request->isGet() && $this->params()->fromQuery('keyword') && $this->params()->fromQuery('keyword')!='') {

                $keyword = $this->params()->fromQuery('keyword');

                $page = 1;
                if ($this->params()->fromQuery('page')&&$this->params()->fromQuery('page')!='')
                {
                    $page = intval($this->params()->fromQuery('page'));
                }

                $recipes = $recipeService->getReicpesByAutoSearch($keyword, 10, ($page - 1)*10);

                $result_recipes = array();
                foreach ($recipes as $recipe){
                    $result_recipe = array(
                        'recipe_id' => $recipe->__get('recipe_id'),
                        'name' => $recipe->__get('name'),
                        'image' => 'images/recipe/140/'.$recipe->__get('cover_img'),
                        'materials' => $recipe->materials,
                        'dish_count' => $recipe->__get('dish_count')
                    );

                    array_push($result_recipes, $result_recipe);
                }

                return new JsonModel(array(
                    'result' => GCFlag::GC_Success,
                    'errorcode' => GCFlag::GC_NoErrorCode,
                    'result_recipes' => $result_recipes,
                ));

            } else {
                return new JsonModel(array(
                    'result' => GCFlag::GC_Failed,
                    'errorcode' => GCFlag::GC_GetParamInvalid,
                ));
            }
        } else {
            return new JsonModel(array(
                'result' => GCFlag::GC_Failed,
                'errorcode' => GCFlag::GC_NoMobileDevice,
            ));
        }
    }


	public function androidUpdateAction()
	{
		return new JsonModel(array(
               'version' => 1113,
               'url' => '',
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

        if (is_array($array)) {
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

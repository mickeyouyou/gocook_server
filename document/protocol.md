协议
=============

(客户端协议带http头"x-client-identifier" => "Mobile" 才能正常返回json)
###用户相关
------------

######登录:
	protocol: user/login 
	post params: login password
	return: {"result":0, "errorcode":0, "username":"user", icon:"iconurl"}

带用户名（email）和密码登录，返回: result（0为成功，1为失败）, errorcode(暂时只有1)

######注册:
	protocol: user/register 
	post params: email nickname password repassword avatar(optional)
	return: {"result":0, "errorcode":0, "username":"user", "icon":"iconurl"}

avatar为可选，类型为file。返回: result（0为成功，1为失败）, errorcode(1:注册失败;2:email不可用;3:nickname不可用;4:密码格式不对;5:其他)


######ios主页:

	protocol: index/ios_main
	return: {"result":0, "topnew_img":"image-url", "tophot_img":"image-url", "recommend_items":[{"name":"name", "images":"image-url"},…]}
	
ios主页协议。分别返回收总藏数最多的菜谱的图片，最新上传的图片，以及热门搜索的图片。android如果不能用，需要再实现一个。


######搜索:

	protocol: index/search?keyword='keyword'&page='page' 
	return: {"result":0, "result_recipes":[{"recipe_id":1, "name":"recipeName", "image":"image-url", "dish_count":123}...]}
	
搜索协议。从菜谱名，用料，分类中查找有关的菜谱，并返回菜谱id，名字，图片url和收藏数。每次返回10个。


######最新菜谱

	protocol: recipe/topnew?page='page'
	return: {"result":0, "result_recipes":[{"recipe_id":1, "name":"recipeName", "image":"image-url", "dish_count":123}…]}
	
最新菜谱协议。返回内容和搜索一样，每次也是返回10个。


######收藏最多菜谱

	protocol: recipe/topnew?page='page'
	return: {"result":0, "result_recipes":[{"recipe_id":1, "name":"recipeName", "image":"image-url", "dish_count":123}...]}
	
收藏最多菜谱协议。返回内容和搜索一样，每次也是返回10个。


######菜谱详细内容

	protocol: recipe?id='id'
	return: {"result":0, "result_recipe":[{"recipe_id":1, "author_id":1, "author_name":"authorName", "recipe_name":"recipeName", "intro" => "Intro", "collected_count":1, "dish_count":1, "comment_count":1, "cover_image":"image-url", "materials":"Meterials", "steps":[{"no":1,"content":"Content", "img":"img-url"}…], "tips":"Tips"}…]}
	
菜谱详细内容协议。返回内容如上。其中meterials需要单独在客户端解析，结构如"A|B|C||E|"这样，材料和用量成对出现，如果无用量，也要空出位置，例如C后面要空一个，E后面要空一个。


<br />
###跟甲方服务器交互相关协议
------------













######登录

	protocol: user/login
	type: post
	params: `login` string
			`password` string
	return: {"result":0, "errorcode":0, "username":"user", "icon":"iconurl"}
	
password为3des加密后的字符串。

######注册

	protocol: user/register
	type: post
	params: `tel` string
			`nickname` string
			`password` string
			`repassword` string
			`email` string (optional)
			`avatar` file (optional)
	return: {"result":0, "errorcode":0, "username":"user", "icon":"iconurl"}
	
avatar为可选，类型为file；email为可选；password和repassword为3des加密后的字符串。

######商品查询

	protocol: cook/search_wares
	type: get
	params: `keyword` string
			`page` integer
	return: {"result":0, "errorcode":0, "page":1, "total_count":100, "wares":[{"id":1,"name":"name","code":"code","remark":"remark","norm":"norm","unit":"unit","price":"price","image_url":"image_url","deal_method":["method1","method2"]}…]}
	
page从1开始
	
######订购M6商品

	protocol: cook/order
	type: post
	params: `wares` string
	return: {"result":0, "errorcode":0, "order_id":"1111"}
	
发送的wares字段格式为`"Wares":[{"WareId":6745,"Quantity":1,"Remark":"切块洗洗"}]`

	
######历史订单查询

	protocol: cook/his_orders
	type: post
	params: `start_day` string
			`end_day` string
			`page` integer
	return: json {result, errorcode, orders:[id, cust_name, code, delivery_type, delivery_time_type, recv_mobile, cost, create_time, order_wares:[id, name, code, remark, norm, unit, price, image_url, deal_method, quantity, cost]]}
	
start_day和end_day为”yyyy-MM-dd”格式的日期

######查询当天销售额
	protocol: cook/day_sales
	type: get
	return: json {result, errorcode, time, sale_fee, sale_count, condition, remark}
	
time为"yyyy-MM-dd HH:mm:ss"格式的日期。
sale_fee为指定日期的销售额。
sale_count为销售笔数。
condition为是否符合获取优惠券条件 1:符合费用; 0:不符合费用; 2:没有可用促销活动; 3:广告。remark为条件说明。



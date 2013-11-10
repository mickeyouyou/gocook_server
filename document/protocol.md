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
	
	time 		”yyyy-MM-dd HH:mm:ss”格式的服务器时间	sale_fee 	指定日期的销售额
	sale_count	销售笔数
	condition	是否符合获取优惠券条件 1符合费用 0不符合费用 2没有可用促销活动 3广告	remark		是否符合条件说明
######获取优惠券
	protocol: cook/get_coupon
	type: get
	param: `coupon_id`
	return: json {result, errorcode, coupons}
			coupons 包含 {time, eff_day, exp_day, coupon_id, coupon_remark, stores, condition, remark, is_delay, supplier, ktype, status, name, url, img, cctime, ctime, val, wid}
	
    time		”yyyy-MM-dd HH:mm:ss”格式的服务器时间 
    eff_day		”yyyy-MM-dd HH:mm:ss”格式的优惠券生效时间,如果是 延期获取记录,则为延期有效时间
    exp_day		"yyyy-MM-dd HH:mm:ss”格式的优惠券失效时间,如果 是延期获取记录,则为延期失效时间
    coupon		优惠券号,如果是延期获取记录,则为空
    coupon_id	<em>记录id，不管是优惠券还是</em>
    coupon_remark 优惠券描述,如果是延期获取记录,则为延期获取 的信息
    stores 		使用门店
    condition	是否符合获取优惠券条件 1 符合费用 0 不符合费 用 2 没有可用促销活动 3 广告
    remark 		是否符合条件说明
    is_delay 	是否延期获取 1 是 0 否
    supplier 	提供商
    ktype 		0 券 1 广告
    status 		0 无效 1 有效
    name 		券名称
    url 		券信息链接
    img			图片链接
    cctime 		”yyyy-MM-dd HH:mm:ss”格式的客户确认金额服务器时间
    ctime		”yyyy-MM-dd HH:mm:ss”格式的创建时间
    val 		券价值
    wid			对应商品编号
    
    
######延期获取优惠券
	protocol: cook/delay_coupon
	type: get
	return: json {result, errorcode, delay_rst, id, time, eff_day, exp_day, condition, remark}
	
    delay_rst	延期的结果 0: 延期成功 1: 延期未成功 2: 已经延期过 (1后台暂时未做判断，0和2需要处理)
    id			延期获取对应的编号,即获取优惠券接口中的 CouponId
    time		”yyyy-MM-dd HH:mm:ss”格式的服务器时间
    eff_day		”yyyy-MM-dd”格式的优惠券延期生效日期
    exp_day		”yyyy-MM-dd”格式的优惠券延期失效日期
    condition	是否符合获取优惠券条件 1 符合费用 0 不符合费用 2 没有可用促销活动 3 广告
    remark		是否符合条件说明######获取客户拥有的优惠券列表
	protocol: cook/my_coupons
    type: get  
    params: page （我们默认一页10条记录，page从1开始）
	return: json {result, errorcode, page, total_count, coupons}

	page		当前页
    total_count	总记录数
    coupons		具体记录，记录格式为获取优惠券协议中的单条记录（没有result, errorcode这两项）的数组


###Attention！


######登陆Ex
	protocol: cook/login_ex
    type: post  
    params: `data`	M6服务器返回的数据 
    		`rnd`	随机数
	return: json {result, errorcode, username, icon}

具体的流程为：
	
	1. 客户端用webview加载M6登录网址`http://o.m6fresh.com/ws/mobile_reg.aspx?sid=xxx`，并随机生成一个整数rnd，作为这个url里的get参数。打开后在网页上进行注册登录。
	2. 登录成功后M6会重定位到`http://o.m6fresh.com/ws/appcallback.aspx`，分享厨房登录验证数据在html中`<input name="tb_data" ...>`的value里。
	3. 取出该数据，用post的方式把之前生成的随机数rnd和分享厨房验证数据data发到分享厨房的登陆协议`cook/login_ex`，后续流程不变
	
	
	
	
	


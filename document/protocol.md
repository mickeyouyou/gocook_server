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


	



	
	

协议
=============

(客户端协议带http头"x-client-identifier" => "Mobile" 才能正常返回json)
###用户相关
------------

######登录:
	protocol: user/login 
	post params: login password
	return: [result:0, errorcode:0, username:"user", icon:"iconurl"]

带用户名（email）和密码登录，返回: result（0为成功，1为失败）, errorcode(暂时只有1)

######注册:
	protocol: user/register 
	post params: email nickname password repassword avatar(optional)
	return: [result:0, errorcode:0, username:"user", icon:"iconurl"]

avatar为可选，类型为file。返回: result（0为成功，1为失败）, errorcode(1:注册失败;2:email不可用;3:nickname不可用;4:密码格式不对;5:其他)
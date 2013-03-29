协议
=============

###用户相关
------------

######登录:
	protocol: user/login 
	post params: login password
	return: [result:0, errorcode:0, username:"user", icon:"iconurl"]

带用户名（email）和密码登录，返回: result（0为成功，1为失败）, errorcode(暂时只有1)

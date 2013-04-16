如何操作
=============

##Introduction
------------
项目主要用到了两个框架，zf2和doctrine。

zf2基于mvc架构，最早这套架构是基于ruby的框架rails提出的，适合小型网站的快速开发。因为php没有任何框架，因此就全盘照抄过来。这类框架的最大的特点就是约定大于配置。

doctrine是一个ORM，最大的好处就是即使底层的数据库换掉了，上层逻辑不用修改，包括你写的DQL语句。也不用纠结不同数据库之间的语法差异。


##如何去实现功能
------------

#####结构
zf在2.0以后强化了module的概念。

一个module就是一个相对独立的功能模块。每个module下面都是一个mvc结构。

例如User Module中，User/src/User/Controller就是C，User/src/User/Entity就是M，而User/view就是V。

Module.php主要是进行module的一些初始化的全局操作；User/config里面是模块的一些配置文件；User/src/User/Form是表单，主要是在view中使用，因为我们是自己在客户端构造post，因此表单只是用来验证客户端提交的post是否有效；User/src/User/Service主要负责将一些比较复杂的操作封装成函数，供Controller调用，这样尽量保证Controller不过于复杂；User/src/User/Repository是一些自定义的Model操作接口，而Model本身不提供操作函数。

每个Module下面都有一些Controller文件，在每个Controller.php中，都有一些Action函数。这样Module，Controller，Action就被映射为一个url
	
	http://myhost/:module/:controller/:action
	
也可以重写route规则，在User/config/module.config.php中就重写了一部分规则，在module之前又加上了:language，当然本项目用不着。

Module.php下面可以进行一些全局的初始化操作，例如在User下面的Module.php中，在getServiceConfig函数中，就初始化了一个叫做"user_service"的服务，其实就是去实例化了一个UserService对象并把它注册到ServiceManager中去，这样在Controller中就能通过$userService = $this->getServiceLocator()->get('user_service')来取到这个对象了。

#####实现步骤

先要确定协议的功能位置，在Main/Controller下有多个controller，一些通用的就放在indexController中，例如主界面的数据。RecipeController管理recipe和recipe comments，DishController管理dish和dish comment，CookController管理包括个人收藏，个人菜谱，个人Dish等等的一些信息。UserController就是负责登陆注册和个人信息。

如果协议是get的话，那么直接在controller里加入相应的协议action，如果是post的话，就需要构造一个form和相应的filter，用来验证用户上报的数据是否符合filter里的限制。

controller主要负责验证post数据，下发数据给用户，以及跳转，如果涉及到比较复杂的逻辑时，应该新建一个相应的Service类，在这个类里完成相应的逻辑。

在Service类中我们通常主要是进行数据库的操作，如果涉及到查询条件比较复杂，或者多表查询时，应该新建一个Repository继承EntityRepository，并在Entity中设置指向该类。

理论上Entity类只负责映射数据库表，变量跟字段匹配，以及表与表之间的关系。里面不涉及业务相关的逻辑。


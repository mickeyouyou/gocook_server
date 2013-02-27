gocook_server
=============

Introduction
------------
This is gocook_server code.


Installation
------------


Apache
----------------------------



php (>=5.3)
----------------------------



zend framework
----------------------------
Install (It will take some time depends on your network)

	cd my/project/dir
	git clone git@github.com:maybe/gocook_server.git
	cd gocook_server
	php composer.phar self-update
	php composer.phar install
	
Database
----------------------------
1. import `data/schema.sql` to mysql

2. copy `config/autoload/database.local.php.dist` and rename the file as `database.local.php`. Remeber to change the user and password.


Virtual Host
------------
Afterwards, set up a virtual host to point to the public/ directory of the
project and you should be ready to go!

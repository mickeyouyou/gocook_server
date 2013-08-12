gocook_server
=============

##Introduction
------------
This is gocook_server code.


##Installation
------------


###Apache & PHP(>=5.3) & Mysql
----------------------------
#####For linux(ubuntu):
	sudo apt-get update
	sudo apt-get install git
	sudo apt-get install apache2
	sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt
	sudo apt-get install mysql-server libapache2-mod-auth-mysql php5-mysql

#####For MacOSX:
Just Install binary mysql


###Virtual Host
------------

1. Change Document
	
	For linux:
	
		sudo gedit /etc/apache2/sites-enabled/000-default 
	
	For Mac:
		
		sudo vi /etc/apache2/httpd.conf
	
	Then Modify as below
    
    	ServerName my.local  						=>add this line or remove `#` before
		<VirtualHost *:80>
		ServerAdmin webmaster@localhost

		DocumentRoot /var/www						=>change to "my/proj/dir/gocook_server/public" (attention: "~" is not allowed)
		<Directory />
			Options FollowSymLinks
			AllowOverride None						=>changge "None" to "All"	
		</Directory>
		<Directory /var/www/>						=>change to "my/proj/dir/gocook_server/public/" (attention: "~" is not allowed)
			Options Indexes FollowSymLinks MultiViews
			AllowOverride None						=>changen "None" to "All"
			Order allow,deny
			allow from all
		</Directory>

2. Change default encoding (Just For linux)
	
		sudo gedit /etc/apache2/conf.d/charset

	remove `#`
	
		#AddDefaultCharset UTF-8
	
3. Enable rewrite mod
	
	For linux	
	
		sudo a2enmod rewrite
	
	For Mac, remove `#` before
	
		#LoadModule rewrite_module libexec/apache2/mod_rewrite.so
		
4. Enable php mod
	For linux
		
		// todo:
	
	For Mac, remove `#` before

		#LoadModule php5_module libexec/apache2/libphp5.so	
5. Modify php.ini
	
	For Mac, remove `;` before `;date.timezone`, and modify as
	
		date.timezone = Asia/Shanghai

6. Remember to restart apache
	
		sudo apachectl restart



##Zend Framework
----------------------------
Install (It will take some time depends on your network, so just relax and have a cup of tea)

	cd my/project/dir
	git clone git@github.com:maybe/gocook_server.git
	cd gocook_server
	php composer.phar install


Then you should make some change.

	mkdir my/proj/dir/gocook_server/data/cache
	mkdir my/proj/dir/gocook_server/data/DoctrineORMModule
	
	chmod 777 my/proj/dir/gocook_server/data/cache
	chmod 777 my/proj/dir/gocook_server/data/DoctrineORMModule

###Config Project Database
----------------------------
1. if you don't have mysql, please install it. After that,
	
		vi ~/.bash_profile
		
	if you don't have this file, just touch it, then copy this into the file.
	
		[[ ~/.bashrc ]] && source ~/.bashrc
		PATH="/usr/local/bin:$PATH"
		export PATH=$PATH:/usr/local/mysql/bin

2. if there's error like `Error 2002(HY000): Can't connect to local MySQL server through socket '/tmp/mysql.sock'`, link the file from `/var`

		sudo ln -s /var/mysql/mysql.sock /tmp/mysql.sock 


1. import `data/schema.sql` to mysql

2. copy `config/autoload/database.local.php.dist` and rename the file as `database.local.php`. Remeber to change the user and password.
2. copy `config/autoload/zenddevelopertools.local.php.dist` and rename the file as `zenddevelopertools.local.php`.

###Finish
Afterwards, you should be ready to go!

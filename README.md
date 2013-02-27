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
		
	


###Zend Framework
----------------------------
Install (It will take some time depends on your network)

	cd my/project/dir
	git clone git@github.com:maybe/gocook_server.git
	cd gocook_server
	php composer.phar self-update
	php composer.phar install


###Config Project Database
----------------------------
1. import `data/schema.sql` to mysql

2. copy `config/autoload/database.local.php.dist` and rename the file as `database.local.php`. Remeber to change the user and password.


###Finish
Afterwards, you should be ready to go!

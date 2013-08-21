## Install instructions

There is several dependencies. You must have ofcourse Apache with rewrite engine enabled (not tested with anything else) and PHP but also MySQL database and Gearman. Gearman worker and job server doesn't have to be on same server, but you have to have Gearman PHP module. Worker is called from Ajax-controller to do it's work and throws error if there isn't Gearman modules installed. You also need [Nexmo](http://nexmo.com) account for sms sending and receiving.

Once you have prerequisites fulfilled, make a database and create application/config/database.php by cloning the sample. Fill the login information and database name in and change server if not localhost. Save and close the file.
Then, create application/config/auth.php by cloning the sample. Fill the information, secret is used in login. The login prompt will take md5-sum from password and sends the sum to the server. Server adds the secret to the end of sum and takes sha1-sum from that. And that's what is in the database. So, fill there something like 100 characters of all kind or something. Analytics refer to Google analytics code but is not used at the moment. Nexmo data can be acquired from Nexmos website, the number is the alphanumerinc string which system will use as sender. If you have bought number from nexmo, fill it there. Salt is for cookies, same thing as secret. Save and close that file also.

Then chmod 777 these directories: files, application/logs and application/cache. If these folders does not exist yet, create them. All other files needs to be world-readable and folders accessible. 

Then, edit the .htaccess-file and change the rewrite-base according to relative url, and last line to `SetEnv KOHANA_ENV PRODUCTION`. The system is coded so that you can switch between development and production systems just by changing that one word. Save and close the file. Related to that, open application/bootstrap.php. Edit line 74 according to your documentroot-folder. Also, edit line 117 if your relative url is something else than / in production.

Ok, now the filesystem is a-ok, so we can move on to the database.

Make connection to the database and copy-paste following:

	DROP TABLE IF EXISTS `config`;
	/*!40101 SET @saved_cs_client     = @@character_set_client */;
	/*!40101 SET character_set_client = utf8 */;
	CREATE TABLE `config` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `opt` tinytext NOT NULL,
	  `value` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	LOCK TABLES `config` WRITE;
	/*!40000 ALTER TABLE `config` DISABLE KEYS */;
	INSERT INTO `config` VALUES (1,'show_tv',0),(2,'show_stream',1);
	/*!40000 ALTER TABLE `config` ENABLE KEYS */;
	UNLOCK TABLES;

	DROP TABLE IF EXISTS `kayttajat`;
	/*!40101 SET @saved_cs_client     = @@character_set_client */;
	/*!40101 SET character_set_client = utf8 */;
	CREATE TABLE `kayttajat` (
	  `u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `kayttis` tinytext NOT NULL,
	  `passu` tinytext NOT NULL,
	  `level` int(11) NOT NULL,
	  PRIMARY KEY (`u_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	/*!40101 SET character_set_client = @saved_cs_client */;

	LOCK TABLES `kayttajat` WRITE;
	/*!40000 ALTER TABLE `kayttajat` DISABLE KEYS */;
	INSERT INTO `kayttajat` VALUES (1,'<your username>','<your password hash>',4);
	/*!40000 ALTER TABLE `kayttajat` ENABLE KEYS */;
	UNLOCK TABLES;

Remember to replace your username and password hash above. You can use code bellow to generate the hash (texts will not be hidden, make sure no-one is eyesdropping). Replace the secret and run it from command line like php password.php.

	<?php
	print"Enter your password: ";
	$line = trim(fgets(STDIN));
	$hash = md5($line);
	$mysqlhash = sha1($hash.'<your secret>');
	print"\nHash ready for database: ".$mysqlhash."\n";
	?>

After these, you should be able to log in. The system will make additional database tables by itself (and needs all permissions to database because of that). If you are greeted by internal server error 500, check the apache error log, and internal log in application/logs/`<year>`/`<month>`/`<day>`.php.
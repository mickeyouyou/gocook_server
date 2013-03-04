--
-- Current Database: `GoCook`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `GoCook` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `GoCook`;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `display_name` varchar(50) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `qq_openid` varchar(255) DEFAULT NULL,
  `qq_access_token` varchar(255) DEFAULT NULL,
  `weibo_id` varchar(255) DEFAULT NULL,
  `weibo_access_token` varchar(255) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `test_field` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `qq_openid` (`qq_openid`),
  UNIQUE KEY `weibo_id` (`weibo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

-- INSERT INTO `user` (`user_id`, `username`, `email`, `display_name`, `password`, `state`, `user_type`)
-- VALUES
-- 	(1,NULL,'aa@aa.com',NULL,'$2y$14$IMWe.Hiz9KpK2OqitxF6LOxNR6Vg3oGk/gzzXkq6kRU1R/3/DTjuW',NULL,NULL);

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

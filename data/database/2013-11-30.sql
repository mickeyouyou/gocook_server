-- MySQL dump 10.13  Distrib 5.6.7-rc, for osx10.7 (i386)
--
-- Host: localhost    Database: GoCook
-- ------------------------------------------------------
-- Server version	5.6.7-rc

--
-- Table structure for table `user_like`
--

DROP TABLE IF EXISTS `user_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_like` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `recipe_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`recipe_id`),
  KEY `user_id` (`user_id`),
  KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


alter table recipe add like_count int(11) after collected_count;
alter table recipe modify collected_count int(11) default 0;
alter table recipe modify like_count int(11) default 0;
alter table recipe modify dish_count int(11) default 0;
alter table recipe modify comment_count int(11) default 0;
alter table recipe modify browse_count int(11) default 0;


alter table user_info modify collect_count int(11) default 0;
alter table user_info modify dish_count int(11) default 0;
alter table user_info modify recipe_count int(11) default 0;
alter table user_info modify following_count int(11) default 0;
alter table user_info modify followed_count int(11) default 0;

alter table user_relation modify state smallint(1) default 0;
alter table user_favor modify state smallint(1) default 0;
alter table user_comment modify state smallint(1) default 0;
alter table recipe_comment modify state smallint(1) default 0;

alter table user modify age smallint(6) default 0;
alter table user modify gender smallint(1) default 0;
alter table user modify state smallint(1) default 0;
alter table user modify user_type smallint(1) default 0;

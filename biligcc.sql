-- MySQL dump 10.16  Distrib 10.1.11-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: biligcc
-- ------------------------------------------------------
-- Server version	10.1.11-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型',
  `url` varchar(255) NOT NULL,
  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型ID',
  `content` text NOT NULL,
  `createdTime` int(10) NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement`
--

LOCK TABLES `announcement` WRITE;
/*!40000 ALTER TABLE `announcement` DISABLE KEYS */;
INSERT INTO `announcement` VALUES (1,1,'course','',1453264440,1453343400,2,'<p>سەرخىل دەرسلىكلەر بېكىتىمىزدە</p>\n',1453264528,0),(2,1,'global','http://bilig.cc/',1453258200,1453272600,0,'سەرخىل دەسلىكلەر بېكىتىمىزدە!',1453264893,0);
/*!40000 ALTER TABLE `announcement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目',
  `tagIds` tinytext COMMENT 'tag标签',
  `source` varchar(1024) DEFAULT '' COMMENT '来源',
  `sourceUrl` varchar(1024) DEFAULT '' COMMENT '来源URL',
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `body` text,
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `originalThumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图原图',
  `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '文章添加/编辑时，如文章中有图片保存',
  `status` enum('published','unpublished','trash') NOT NULL DEFAULT 'unpublished' COMMENT '状态',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '头条',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `upsNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article`
--

LOCK TABLES `article` WRITE;
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
INSERT INTO `article` VALUES (1,'بىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇ',1,'|1|','','',1453264972,'<p>بىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇ</p>\r\n','public://default/2016/01-20/12440114a873414150.jpg','public://article/2016/01-20/12440116a252358093.jpg','','published',0,0,0,0,0,0,1,1453265043,1453265043),(2,'سەرخىل دەسلىكلەر بېكىتىمىزدە',1,'|1|','','',1453265065,'<p>بىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇبىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇ</p>\r\n','public://default/2016/01-20/1244480e2d66175177.jpg','public://article/2016/01-20/12444910a64b862679.jpg','','published',8,0,0,0,0,0,1,1453265090,1454597894);
/*!40000 ALTER TABLE `article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_category`
--

DROP TABLE IF EXISTS `article_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '栏目名称',
  `code` varchar(64) NOT NULL COMMENT 'URL目录名称',
  `weight` int(11) NOT NULL DEFAULT '0',
  `publishArticle` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许发布文章',
  `seoTitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目标题',
  `seoKeyword` varchar(1024) NOT NULL DEFAULT '' COMMENT 'SEO 关键字',
  `seoDesc` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目描述（SEO）',
  `published` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（1：启用 0：停用)',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_category`
--

LOCK TABLES `article_category` WRITE;
/*!40000 ALTER TABLE `article_category` DISABLE KEYS */;
INSERT INTO `article_category` VALUES (1,'بىلىگ خەۋەرلىرى','news',0,1,'','','',1,0,1453264969);
/*!40000 ALTER TABLE `article_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_like`
--

DROP TABLE IF EXISTS `article_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_like` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `articleId` int(10) unsigned NOT NULL COMMENT '资讯id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资讯点赞表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_like`
--

LOCK TABLES `article_like` WRITE;
/*!40000 ALTER TABLE `article_like` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_like` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batch_notification`
--

DROP TABLE IF EXISTS `batch_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '群发通知id',
  `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '通知类型',
  `title` text NOT NULL COMMENT '通知标题',
  `fromId` int(10) unsigned NOT NULL COMMENT '发送人id',
  `content` text NOT NULL COMMENT '通知内容',
  `targetType` text NOT NULL COMMENT '通知发送对象group,global,course,classroom等',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知发送对象ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送通知时间',
  `sendedTime` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知的发送时间',
  `published` int(10) NOT NULL DEFAULT '0' COMMENT '是否已经发送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='群发通知表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batch_notification`
--

LOCK TABLES `batch_notification` WRITE;
/*!40000 ALTER TABLE `batch_notification` DISABLE KEYS */;
INSERT INTO `batch_notification` VALUES (1,'text','بىلىگ دەرسخانىسى',1,'<p>سەرخىل دەرسلىكلەر بېكىتىمىزدە</p>\r\n','global',0,1453264918,1453264923,1);
/*!40000 ALTER TABLE `batch_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist`
--

DROP TABLE IF EXISTS `blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userId` int(10) unsigned NOT NULL COMMENT '名单拥有者id',
  `blackId` int(10) unsigned NOT NULL COMMENT '黑名单用户id',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入黑名单时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist`
--

LOCK TABLES `blacklist` WRITE;
/*!40000 ALTER TABLE `blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block`
--

DROP TABLE IF EXISTS `block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编辑区ID',
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `mode` enum('html','template') NOT NULL DEFAULT 'html' COMMENT '模式',
  `template` text COMMENT '模板',
  `templateName` varchar(255) DEFAULT NULL COMMENT '编辑区模板名字',
  `templateData` text COMMENT '模板数据',
  `content` text COMMENT '内容',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编码',
  `meta` text COMMENT '编辑区元信息',
  `data` text COMMENT '编辑区内容',
  `tips` text,
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='编辑区';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block`
--

LOCK TABLES `block` WRITE;
/*!40000 ALTER TABLE `block` DISABLE KEYS */;
INSERT INTO `block` VALUES (1,1,'我的账户Banner','html',NULL,NULL,NULL,'<br>\n<div class=\"col-md-12\">\n  \n        <a href=\"#\"><img src=\"/assets/img/placeholder/banner-wallet.png\" style=\"width: 100%;\"/></a>\n        <br>\n<br>\n</div>','bill_banner',NULL,NULL,NULL,1452851967,1452851967,'system'),(2,0,'直播频道 - 首页 - 头部轮播图','template',NULL,'TopxiaWebBundle:Block:live_top_banner.template.html.twig',NULL,'  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-2.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-2.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n','live_top_banner','{\"title\":\"\\u76f4\\u64ad\\u9891\\u9053 - \\u9996\\u9875 - \\u5934\\u90e8\\u8f6e\\u64ad\\u56fe\",\"category\":\"system\",\"templateName\":\"TopxiaWebBundle:Block:live_top_banner.template.html.twig\",\"items\":{\"carousel\":{\"title\":\"\\u8f6e\\u64ad\\u56fe\",\"desc\":\"\\u5efa\\u8bae\\u4f7f\\u7528715x310\\u5927\\u5c0f\\u7684\\u56fe\\u7247\\uff0c\\u6700\\u591a\\u53ef\\u8bbe\\u7f6e\\uff15\\u5f20\\u56fe\\u7247\\u3002\",\"count\":5,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff11\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff12\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe4\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe5\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"}]}}}','{\"carousel\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff11\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff12\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe4\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe5\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"}]}','',1452852366,1452852822,'system'),(3,0,'默认主题：首页头部图片轮播','template',NULL,'@theme/default/block/home_top_banner.template.html.twig',NULL,'  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-1.png\" alt=\"轮播图1描述\"></a>\n  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-2.png\" alt=\"轮播图2描述\"></a>\n  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-3.png\" alt=\"轮播图3描述\"></a>\n','default:home_top_banner','{\"title\":\"\\u9ed8\\u8ba4\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u5934\\u90e8\\u56fe\\u7247\\u8f6e\\u64ad\",\"category\":\"default\",\"templateName\":\"@theme\\/default\\/block\\/home_top_banner.template.html.twig\",\"items\":{\"carousel\":{\"title\":\"\\u8f6e\\u64ad\\u56fe\",\"desc\":\"\\u5efa\\u8bae\\u4f7f\\u75281200x256\\u5927\\u5c0f\\u7684\\u56fe\\u7247\\uff0c\\u6700\\u591a\\u53ef\\u6dfb\\u52a0\\uff15\\u5f20\\u56fe\\u7247\\u3002\",\"count\":5,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-1.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe1\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-2.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe2\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-3.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}}}','{\"carousel\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-1.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe1\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-2.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe2\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-3.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}','',1452852367,1452852822,'default'),(4,0,'清秋主题：首页头部图片轮播','template',NULL,'@theme/autumn/block/carousel.template.html.twig',NULL,'  <div class=\"item active\">\n    <a href=\"/#?6.12.2\" target=\"_blank\"><img src=\"/themes/autumn/img/slide-1.jpg?6.12.2\" alt=\"图片１的描述\"></a>\n  </div>\n  <div class=\"item \">\n    <a href=\"/#?6.12.2\" target=\"_self\"><img src=\"/themes/autumn/img/slide-2.jpg?6.12.2\" alt=\"图片２的描述\"></a>\n  </div>\n  <div class=\"item \">\n    <a href=\"/#?6.12.2\" target=\"_blank\"><img src=\"/themes/autumn/img/slide-3.jpg?6.12.2\" alt=\"图片３的描述\"></a>\n  </div>\n','autumn:home_top_banner','{\"title\":\"\\u6e05\\u79cb\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u5934\\u90e8\\u56fe\\u7247\\u8f6e\\u64ad\",\"category\":\"autumn\",\"templateName\":\"@theme\\/autumn\\/block\\/carousel.template.html.twig\",\"items\":{\"carousel\":{\"title\":\"\\u8f6e\\u64ad\\u56fe\",\"desc\":\"\\u5efa\\u8bbe\\u4f7f\\u75281920x300\\u5927\\u5c0f\\u7684\\u56fe\\u7247\\uff0c\\u6700\\u591a\\u53ef\\u8bbe\\u7f6e\\uff15\\u5f20\\u56fe\\u7247\\u3002\",\"count\":5,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/themes\\/autumn\\/img\\/slide-1.jpg\",\"alt\":\"\\u56fe\\u7247\\uff11\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-2.jpg\",\"alt\":\"\\u56fe\\u7247\\uff12\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-3.jpg\",\"alt\":\"\\u56fe\\u7247\\uff13\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}}}','{\"carousel\":[{\"src\":\"\\/themes\\/autumn\\/img\\/slide-1.jpg\",\"alt\":\"\\u56fe\\u7247\\uff11\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-2.jpg\",\"alt\":\"\\u56fe\\u7247\\uff12\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-3.jpg\",\"alt\":\"\\u56fe\\u7247\\uff13\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}','',1452852367,1452852822,'autumn'),(5,0,'简墨主题：首页顶部.轮播图 ','template',NULL,'@theme/jianmo/block/carousel.template.html.twig',NULL,'<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #3ec768;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>','jianmo:home_top_banner','{\"title\":\"\\u7b80\\u58a8\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u9876\\u90e8.\\u8f6e\\u64ad\\u56fe \",\"category\":\"jianmo\",\"templateName\":\"@theme\\/jianmo\\/block\\/carousel.template.html.twig\",\"items\":{\"posters\":{\"title\":\"\\u6d77\\u62a5\",\"desc\":\"\\u9996\\u9875\\u6d77\\u62a5\",\"count\":1,\"type\":\"poster\",\"default\":[{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}}}','{\"posters\":[{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}','',1452852367,1452852822,'jianmo'),(6,0,'简墨主题：首页中部.横幅','template',NULL,'@theme/jianmo/block/middle_banner.template.html.twig',NULL,'<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>','jianmo:middle_banner','{\"title\":\"\\u7b80\\u58a8\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u4e2d\\u90e8.\\u6a2a\\u5e45\",\"category\":\"jianmo\",\"templateName\":\"@theme\\/jianmo\\/block\\/middle_banner.template.html.twig\",\"items\":{\"icon1\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff11\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon1title\":{\"title\":\"\\u56fe\\u6807\\uff11\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927\"}]},\"icon1introduction\":{\"title\":\"\\u56fe\\u6807\\uff11\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}]},\"icon2\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff12\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon2title\":{\"title\":\"\\u56fe\\u6807\\uff12\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}]},\"icon2introduction\":{\"title\":\"\\u56fe\\u6807\\uff12\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}]},\"icon3\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff13\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon3title\":{\"title\":\"\\u56fe\\u6807\\uff13\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}]},\"icon3introduction\":{\"title\":\"\\u56fe\\u6807\\uff13\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}}}','{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927\"}],\"icon1introduction\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}],\"icon2introduction\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}],\"icon3introduction\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}','',1452852368,1452852822,'jianmo'),(7,0,'简墨主题: 首页底部.链接区域','template',NULL,'@theme/jianmo/block/bottom_info.template.html.twig',NULL,'\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n','jianmo:bottom_info','{\"title\":\"\\u7b80\\u58a8\\u4e3b\\u9898: \\u9996\\u9875\\u5e95\\u90e8.\\u94fe\\u63a5\\u533a\\u57df\",\"category\":\"jianmo\",\"templateName\":\"@theme\\/jianmo\\/block\\/bottom_info.template.html.twig\",\"items\":{\"firstColumnText\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}]},\"firstColumnLinks\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}]},\"secondColumnText\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}]},\"secondColumnLinks\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}]},\"thirdColumnText\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}]},\"thirdColumnLinks\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}]},\"fourthColumnText\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}]},\"fourthColumnLinks\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}]},\"fifthColumnText\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}]},\"fifthColumnLinks\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}]},\"bottomLogo\":{\"title\":\"\\u5e95\\u90e8Logo\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"count\":1,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}]},\"weibo\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u535a\\u94fe\\u63a5\",\"desc\":\"\\u586b\\u5165\\u7f51\\u6821\\u7684\\u5fae\\u535a\\u9996\\u9875\\u5730\\u5740\",\"count\":1,\"type\":\"link\",\"default\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}]},\"weixin\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\\u7684\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}]},\"apple\":{\"title\":\"\\u5e95\\u90e8iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}]},\"android\":{\"title\":\"\\u5e95\\u90e8Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}}}','{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','',1452852368,1452852822,'jianmo'),(8,1,'必利主题：首页顶部.轮播图 ','template',NULL,'@theme/bilig/block/carousel.template.html.twig',NULL,'<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #3ec768;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/bilig/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/bilig/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/bilig/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>','bilig:home_top_banner','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u9876\\u90e8.\\u8f6e\\u64ad\\u56fe \",\"category\":\"bilig\",\"templateName\":\"@theme\\/bilig\\/block\\/carousel.template.html.twig\",\"items\":{\"posters\":{\"title\":\"\\u6d77\\u62a5\",\"desc\":\"\\u9996\\u9875\\u6d77\\u62a5\",\"count\":1,\"type\":\"poster\",\"default\":[{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}}}','{\"posters\":[{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/bilig\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}','',1453215900,1453215900,'bilig'),(9,1,'必利主题：首页中部.横幅','template',NULL,'@theme/bilig/block/middle_banner.template.html.twig',NULL,'<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>','bilig:middle_banner','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u4e2d\\u90e8.\\u6a2a\\u5e45\",\"category\":\"bilig\",\"templateName\":\"@theme\\/bilig\\/block\\/middle_banner.template.html.twig\",\"items\":{\"icon1\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff11\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon1title\":{\"title\":\"\\u56fe\\u6807\\uff11\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927\"}]},\"icon1introduction\":{\"title\":\"\\u56fe\\u6807\\uff11\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}]},\"icon2\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff12\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon2title\":{\"title\":\"\\u56fe\\u6807\\uff12\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}]},\"icon2introduction\":{\"title\":\"\\u56fe\\u6807\\uff12\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}]},\"icon3\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff13\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon3title\":{\"title\":\"\\u56fe\\u6807\\uff13\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}]},\"icon3introduction\":{\"title\":\"\\u56fe\\u6807\\uff13\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}}}','{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927\"}],\"icon1introduction\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}],\"icon2introduction\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}],\"icon3introduction\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}','',1453215901,1453215901,'bilig'),(10,1,'必利主题: 首页底部.链接区域','template',NULL,'@theme/bilig/block/bottom_info.template.html.twig',NULL,'\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n','bilig:bottom_info','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898: \\u9996\\u9875\\u5e95\\u90e8.\\u94fe\\u63a5\\u533a\\u57df\",\"category\":\"bilig\",\"templateName\":\"@theme\\/bilig\\/block\\/bottom_info.template.html.twig\",\"items\":{\"firstColumnText\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}]},\"firstColumnLinks\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}]},\"secondColumnText\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}]},\"secondColumnLinks\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}]},\"thirdColumnText\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}]},\"thirdColumnLinks\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}]},\"fourthColumnText\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}]},\"fourthColumnLinks\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}]},\"fifthColumnText\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}]},\"fifthColumnLinks\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}]},\"bottomLogo\":{\"title\":\"\\u5e95\\u90e8Logo\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"count\":1,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}]},\"weibo\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u535a\\u94fe\\u63a5\",\"desc\":\"\\u586b\\u5165\\u7f51\\u6821\\u7684\\u5fae\\u535a\\u9996\\u9875\\u5730\\u5740\",\"count\":1,\"type\":\"link\",\"default\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}]},\"weixin\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\\u7684\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}]},\"apple\":{\"title\":\"\\u5e95\\u90e8iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}]},\"android\":{\"title\":\"\\u5e95\\u90e8Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}}}','{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','',1453215901,1453215901,'bilig'),(11,1,'必利主题：首页顶部.轮播图 ','template',NULL,'@theme/biligcc/block/carousel.template.html.twig',NULL,'<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122410a88557276811.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122359f4e374523128.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122325d98b0e172705.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>','biligcc:home_top_banner','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u9876\\u90e8.\\u8f6e\\u64ad\\u56fe \",\"category\":\"biligcc\",\"templateName\":\"@theme\\/biligcc\\/block\\/carousel.template.html.twig\",\"items\":{\"posters\":{\"title\":\"\\u6d77\\u62a5\",\"desc\":\"\\u9996\\u9875\\u6d77\\u62a5\",\"count\":1,\"type\":\"poster\",\"default\":[{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}}}','{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122410a88557276811.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122359f4e374523128.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','',1453216165,1453955052,'biligcc'),(12,1,'必利主题：首页中部.横幅','template',NULL,'@theme/biligcc/block/middle_banner.template.html.twig',NULL,'<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>','biligcc:middle_banner','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898\\uff1a\\u9996\\u9875\\u4e2d\\u90e8.\\u6a2a\\u5e45\",\"category\":\"biligcc\",\"templateName\":\"@theme\\/biligcc\\/block\\/middle_banner.template.html.twig\",\"items\":{\"icon1\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff11\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon1title\":{\"title\":\"\\u56fe\\u6807\\uff11\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649 \\u0626\\u0649\\u0642\\u062a\\u0649\\u062f\\u0627\\u0631\\u0649 \\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643\"}]},\"icon1introduction\":{\"title\":\"\\u56fe\\u6807\\uff11\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u062a\\u06c8\\u0645\\u06d5\\u0646\\u0644\\u0649\\u06af\\u06d5\\u0646 \\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649\\u0646\\u0649\\u06ad \\u0626\\u0648\\u0631\\u062a\\u0627\\u0642 \\u062a\\u0627\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0626\\u0649\\u0634\\u0649\\u0646\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u06af\\u06d5 \\u0626\\u06d5\\u0631\\u0632\\u0649\\u064a\\u062f\\u06c7\"}]},\"icon2\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff12\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon2title\":{\"title\":\"\\u56fe\\u6807\\uff12\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649\"}]},\"icon2introduction\":{\"title\":\"\\u56fe\\u6807\\uff12\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649 \\u0626\\u0649\\u0634\\u0644\\u0649\\u062a\\u0649\\u0644\\u0649\\u067e\\u060c \\u0628\\u0627\\u0631\\u0644\\u0649\\u0642 \\u0626\\u06c8\\u0633\\u0643\\u06c8\\u0646\\u0649\\u0644\\u06d5\\u0631\\u06af\\u06d5 \\u0645\\u0627\\u0633\\u0644\\u0627\\u0634\\u062a\\u06c7\\u0631\\u06c7\\u0644\\u062f\\u0649\"}]},\"icon3\":{\"title\":\"\\u4e2d\\u90e8\\u56fe\\u6807\\uff13\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a130*130\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}]},\"icon3title\":{\"title\":\"\\u56fe\\u6807\\uff13\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0645\\u06c7\\u0644\\u0627\\u0632\\u0649\\u0645\\u0649\\u062a\\u0649 \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\"}]},\"icon3introduction\":{\"title\":\"\\u56fe\\u6807\\uff13\\u4ecb\\u7ecd\",\"desc\":\"\",\"count\":1,\"type\":\"textarea\",\"default\":[{\"value\":\"\\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643 \\u0628\\u0648\\u0644\\u063a\\u0627\\u0646 \\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0633\\u0649\\u0632\\u0646\\u0649 \\u063a\\u06d5\\u0645 \\u0626\\u06d5\\u0646\\u062f\\u0649\\u0634\\u0649\\u0644\\u06d5\\u0631\\u062f\\u0649\\u0646 \\u062e\\u0627\\u0644\\u0649 \\u0642\\u0649\\u0644\\u0649\\u062f\\u06c7\"}]}}}','{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649 \\u0626\\u0649\\u0642\\u062a\\u0649\\u062f\\u0627\\u0631\\u0649 \\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643\"}],\"icon1introduction\":[{\"value\":\"\\u062a\\u06c8\\u0645\\u06d5\\u0646\\u0644\\u0649\\u06af\\u06d5\\u0646 \\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649\\u0646\\u0649\\u06ad \\u0626\\u0648\\u0631\\u062a\\u0627\\u0642 \\u062a\\u0627\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0626\\u0649\\u0634\\u0649\\u0646\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u06af\\u06d5 \\u0626\\u06d5\\u0631\\u0632\\u0649\\u064a\\u062f\\u06c7\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649\"}],\"icon2introduction\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649 \\u0626\\u0649\\u0634\\u0644\\u0649\\u062a\\u0649\\u0644\\u0649\\u067e\\u060c \\u0628\\u0627\\u0631\\u0644\\u0649\\u0642 \\u0626\\u06c8\\u0633\\u0643\\u06c8\\u0646\\u0649\\u0644\\u06d5\\u0631\\u06af\\u06d5 \\u0645\\u0627\\u0633\\u0644\\u0627\\u0634\\u062a\\u06c7\\u0631\\u06c7\\u0644\\u062f\\u0649\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0645\\u06c7\\u0644\\u0627\\u0632\\u0649\\u0645\\u0649\\u062a\\u0649 \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\"}],\"icon3introduction\":[{\"value\":\"\\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643 \\u0628\\u0648\\u0644\\u063a\\u0627\\u0646 \\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0633\\u0649\\u0632\\u0646\\u0649 \\u063a\\u06d5\\u0645 \\u0626\\u06d5\\u0646\\u062f\\u0649\\u0634\\u0649\\u0644\\u06d5\\u0631\\u062f\\u0649\\u0646 \\u062e\\u0627\\u0644\\u0649 \\u0642\\u0649\\u0644\\u0649\\u062f\\u06c7\"}]}','',1453216165,1453222579,'biligcc'),(13,1,'必利主题: 首页底部.链接区域','template',NULL,'@theme/biligcc/block/bottom_info.template.html.twig',NULL,'\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n','biligcc:bottom_info','{\"title\":\"\\u5fc5\\u5229\\u4e3b\\u9898: \\u9996\\u9875\\u5e95\\u90e8.\\u94fe\\u63a5\\u533a\\u57df\",\"category\":\"biligcc\",\"templateName\":\"@theme\\/biligcc\\/block\\/bottom_info.template.html.twig\",\"items\":{\"firstColumnText\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}]},\"firstColumnLinks\":{\"title\":\"\\u7b2c\\uff11\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}]},\"secondColumnText\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}]},\"secondColumnLinks\":{\"title\":\"\\u7b2c\\uff12\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}]},\"thirdColumnText\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}]},\"thirdColumnLinks\":{\"title\":\"\\u7b2c\\uff13\\u5217\\u94fe\\u63a5\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}]},\"fourthColumnText\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}]},\"fourthColumnLinks\":{\"title\":\"\\u7b2c\\uff14\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}]},\"fifthColumnText\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":1,\"type\":\"text\",\"default\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}]},\"fifthColumnLinks\":{\"title\":\"\\u7b2c\\uff15\\u5217\\u94fe\\u63a5\\u6807\\u9898\",\"desc\":\"\",\"count\":5,\"type\":\"link\",\"default\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}]},\"bottomLogo\":{\"title\":\"\\u5e95\\u90e8Logo\",\"desc\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"count\":1,\"type\":\"imglink\",\"default\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}]},\"weibo\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u535a\\u94fe\\u63a5\",\"desc\":\"\\u586b\\u5165\\u7f51\\u6821\\u7684\\u5fae\\u535a\\u9996\\u9875\\u5730\\u5740\",\"count\":1,\"type\":\"link\",\"default\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}]},\"weixin\":{\"title\":\"\\u5e95\\u90e8\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\\u7684\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}]},\"apple\":{\"title\":\"\\u5e95\\u90e8iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684iOS\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}]},\"android\":{\"title\":\"\\u5e95\\u90e8Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"desc\":\"\\u4e0a\\u4f20\\u7f51\\u6821\\u7684Android\\u7248APP\\u4e0b\\u8f7d\\u4e8c\\u7ef4\\u7801\",\"count\":1,\"type\":\"img\",\"default\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}}}','{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','',1453216165,1453222579,'biligcc');
/*!40000 ALTER TABLE `block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_history`
--

DROP TABLE IF EXISTS `block_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编辑区历史记录ID',
  `blockId` int(11) NOT NULL COMMENT '编辑区ID',
  `templateData` text COMMENT '模板历史数据',
  `data` text COMMENT 'block元信息',
  `content` text COMMENT '编辑区内容',
  `userId` int(11) NOT NULL COMMENT '编辑人ID',
  `createdTime` int(11) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COMMENT='编辑区历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_history`
--

LOCK TABLES `block_history` WRITE;
/*!40000 ALTER TABLE `block_history` DISABLE KEYS */;
INSERT INTO `block_history` VALUES (1,2,NULL,NULL,NULL,0,1452852366),(2,3,NULL,NULL,NULL,0,1452852367),(3,4,NULL,NULL,NULL,0,1452852367),(4,5,NULL,NULL,NULL,0,1452852367),(5,6,NULL,NULL,NULL,0,1452852368),(6,7,NULL,NULL,NULL,0,1452852368),(7,2,NULL,'{\"carousel\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff11\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe\\uff12\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe4\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg\",\"alt\":\"\\u8f6e\\u64ad\\u56fe5\\u56fe\\u7247\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"}]}','  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-2.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-2.jpg?6.12.2\" /></a>\n  <a href=\"#\"><img src=\"/assets/img/placeholder/live-slide-1.jpg?6.12.2\" /></a>\n',0,1452852822),(8,3,NULL,'{\"carousel\":[{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-1.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe1\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-2.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe2\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-3.png\",\"alt\":\"\\u8f6e\\u64ad\\u56fe3\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}','  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-1.png\" alt=\"轮播图1描述\"></a>\n  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-2.png\" alt=\"轮播图2描述\"></a>\n  <a href=\"#\" target=\"_blank\"><img src=\"/assets/img/placeholder/carousel-1200x256-3.png\" alt=\"轮播图3描述\"></a>\n',0,1452852822),(9,4,NULL,'{\"carousel\":[{\"src\":\"\\/themes\\/autumn\\/img\\/slide-1.jpg\",\"alt\":\"\\u56fe\\u7247\\uff11\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-2.jpg\",\"alt\":\"\\u56fe\\u7247\\uff12\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_self\"},{\"src\":\"\\/themes\\/autumn\\/img\\/slide-3.jpg\",\"alt\":\"\\u56fe\\u7247\\uff13\\u7684\\u63cf\\u8ff0\",\"href\":\"#\",\"target\":\"_blank\"}]}','  <div class=\"item active\">\n    <a href=\"/#?6.12.2\" target=\"_blank\"><img src=\"/themes/autumn/img/slide-1.jpg?6.12.2\" alt=\"图片１的描述\"></a>\n  </div>\n  <div class=\"item \">\n    <a href=\"/#?6.12.2\" target=\"_self\"><img src=\"/themes/autumn/img/slide-2.jpg?6.12.2\" alt=\"图片２的描述\"></a>\n  </div>\n  <div class=\"item \">\n    <a href=\"/#?6.12.2\" target=\"_blank\"><img src=\"/themes/autumn/img/slide-3.jpg?6.12.2\" alt=\"图片３的描述\"></a>\n  </div>\n',0,1452852822),(10,5,NULL,'{\"posters\":[{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #3ec768;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/jianmo/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',0,1452852822),(11,6,NULL,'{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927\"}],\"icon1introduction\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}],\"icon2introduction\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}],\"icon3introduction\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}','<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>',0,1452852822),(12,7,NULL,'{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n',0,1452852822),(13,8,NULL,NULL,NULL,1,1453215900),(14,9,NULL,NULL,NULL,1,1453215901),(15,10,NULL,NULL,NULL,1,1453215901),(16,11,NULL,NULL,NULL,1,1453216165),(17,12,NULL,NULL,NULL,1,1453216165),(18,13,NULL,NULL,NULL,1,1453216165),(19,11,NULL,'{\"posters\":[{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #3ec768;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453221539),(20,12,NULL,'{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649 \\u0626\\u0649\\u0642\\u062a\\u0649\\u062f\\u0627\\u0631\\u0649 \\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643\"}],\"icon1introduction\":[{\"value\":\"\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f\"}],\"icon2introduction\":[{\"value\":\"\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301\"}],\"icon3introduction\":[{\"value\":\"\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7\"}]}','<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>',1,1453221539),(21,13,NULL,'{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n',1,1453221539),(22,11,NULL,'{\"posters\":[{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a51\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\",\"alt\":\"\\u6d77\\u62a52\",\"layout\":\"tile\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"alt\":\"\\u6d77\\u62a53\",\"layout\":\"tile\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\",\"status\":\"1\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a54\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a55\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a56\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a57\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"},{\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"alt\":\"\\u6d77\\u62a58\",\"layout\":\"tile\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\",\"status\":\"0\",\"mode\":\"img\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #3ec768;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453222579),(23,12,NULL,'{\"icon1\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon1title\":[{\"value\":\"\\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649 \\u0626\\u0649\\u0642\\u062a\\u0649\\u062f\\u0627\\u0631\\u0649 \\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643\"}],\"icon1introduction\":[{\"value\":\"\\u062a\\u06c8\\u0645\\u06d5\\u0646\\u0644\\u0649\\u06af\\u06d5\\u0646 \\u062a\\u0648\\u0631 \\u0645\\u06d5\\u0643\\u062a\\u0649\\u06cb\\u0649\\u0646\\u0649\\u06ad \\u0626\\u0648\\u0631\\u062a\\u0627\\u0642 \\u062a\\u0627\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0626\\u0649\\u0634\\u0649\\u0646\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u06af\\u06d5 \\u0626\\u06d5\\u0631\\u0632\\u0649\\u064a\\u062f\\u06c7\"}],\"icon2\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon2title\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649\"}],\"icon2introduction\":[{\"value\":\"\\u0645\\u0627\\u0633\\u0644\\u0649\\u0634\\u0649\\u0634\\u0686\\u0627\\u0646 \\u0628\\u06d5\\u062a \\u062a\\u06d0\\u062e\\u0646\\u0649\\u0643\\u0649\\u0633\\u0649 \\u0626\\u0649\\u0634\\u0644\\u0649\\u062a\\u0649\\u0644\\u0649\\u067e\\u060c \\u0628\\u0627\\u0631\\u0644\\u0649\\u0642 \\u0626\\u06c8\\u0633\\u0643\\u06c8\\u0646\\u0649\\u0644\\u06d5\\u0631\\u06af\\u06d5 \\u0645\\u0627\\u0633\\u0644\\u0627\\u0634\\u062a\\u06c7\\u0631\\u06c7\\u0644\\u062f\\u0649\"}],\"icon3\":[{\"src\":\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\",\"alt\":\"\\u4e2d\\u90e8\\u6a2a\\u5e45\"}],\"icon3title\":[{\"value\":\"\\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0645\\u06c7\\u0644\\u0627\\u0632\\u0649\\u0645\\u0649\\u062a\\u0649 \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\"}],\"icon3introduction\":[{\"value\":\"\\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643 \\u0628\\u0648\\u0644\\u063a\\u0627\\u0646 \\u0645\\u0627\\u0626\\u0627\\u0631\\u0649\\u067e \\u0628\\u06c7\\u0644\\u06c7\\u062a \\u0642\\u0648\\u0644\\u0644\\u0649\\u0634\\u0649\\u060c \\u0633\\u0649\\u0632\\u0646\\u0649 \\u063a\\u06d5\\u0645 \\u0626\\u06d5\\u0646\\u062f\\u0649\\u0634\\u0649\\u0644\\u06d5\\u0631\\u062f\\u0649\\u0646 \\u062e\\u0627\\u0644\\u0649 \\u0642\\u0649\\u0644\\u0649\\u062f\\u06c7\"}]}','<section class=\"introduction-section\">\n  <div class=\"container hidden-xs\">\n    <div class=\"row\">\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_1.png\">\n          <h4>网校功能强大</h4>\n          <h5>一万多家网校共同选择，值得信赖</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_2.png\">\n          <h4>响应式页面技术</h4>\n          <h5>采用响应式技术，完美适配任意终端</h5>\n        </div>\n                                      <div class=\"col-md-4 col-sm-4 col-xs-12 introduction-item\">\n          <img class=\"img-responsive\" src=\"/assets/v2/img/icon_introduction_3.png\">\n          <h4>教育云服务支持</h4>\n          <h5>强力教育云支持，免除你的后顾之忧</h5>\n        </div>\n          </div>\n  </div>\n</section>',1,1453222579),(24,13,NULL,'{\"firstColumnText\":[{\"value\":\"\\u6211\\u662f\\u5b66\\u751f\"}],\"firstColumnLinks\":[{\"value\":\"\\u5982\\u4f55\\u6ce8\\u518c\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u5b66\\u4e60\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\",\"target\":\"_blank\"},{\"value\":\"\\u5982\\u4f55\\u4e92\\u52a8\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\",\"target\":\"_blank\"}],\"secondColumnText\":[{\"value\":\"\\u6211\\u662f\\u8001\\u5e08\"}],\"secondColumnLinks\":[{\"value\":\"\\u53d1\\u5e03\\u8bfe\\u7a0b\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\",\"target\":\"_blank\"},{\"value\":\"\\u4f7f\\u7528\\u9898\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\",\"target\":\"_blank\"},{\"value\":\"\\u6559\\u5b66\\u8d44\\u6599\\u5e93\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\",\"target\":\"_blank\"}],\"thirdColumnText\":[{\"value\":\"\\u6211\\u662f\\u7ba1\\u7406\\u5458\"}],\"thirdColumnLinks\":[{\"value\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\",\"target\":\"_blank\"},{\"value\":\"\\u8bfe\\u7a0b\\u8bbe\\u7f6e\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u7ba1\\u7406\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\",\"target\":\"_blank\"}],\"fourthColumnText\":[{\"value\":\"\\u5546\\u4e1a\\u5e94\\u7528\"}],\"fourthColumnLinks\":[{\"value\":\"\\u4f1a\\u5458\\u4e13\\u533a\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\",\"target\":\"_blank\"},{\"value\":\"\\u9898\\u5e93\\u589e\\u5f3a\\u7248\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\",\"target\":\"_blank\"},{\"value\":\"\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa\",\"href\":\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\",\"target\":\"_blank\"}],\"fifthColumnText\":[{\"value\":\"\\u5173\\u4e8e\\u6211\\u4eec\"}],\"fifthColumnLinks\":[{\"value\":\"ES\\u5b98\\u7f51\",\"href\":\"http:\\/\\/www.edusoho.com\\/\",\"target\":\"_blank\"},{\"value\":\"\\u5b98\\u65b9\\u5fae\\u535a\",\"href\":\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&wvr=6&mod=personinfo\",\"target\":\"_blank\"},{\"value\":\"\\u52a0\\u5165\\u6211\\u4eec\",\"href\":\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\",\"target\":\"_blank\"}],\"bottomLogo\":[{\"src\":\"\\/assets\\/v2\\/img\\/bottom_logo.png\",\"alt\":\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\",\"href\":\"http:\\/\\/www.edusoho.com\",\"target\":\"_blank\"}],\"weibo\":[{\"value\":\"\\u5fae\\u535a\\u9996\\u9875\",\"href\":\"http:\\/\\/weibo.com\\/edusoho\",\"target\":\"_blank\"}],\"weixin\":[{\"src\":\"\\/assets\\/img\\/default\\/weixin.png\",\"alt\":\"\\u5fae\\u4fe1\\u516c\\u4f17\\u53f7\"}],\"apple\":[{\"src\":\"\\/assets\\/img\\/default\\/apple.png\",\"alt\":\"\\u7f51\\u6821\\u7684iOS\\u7248APP\"}],\"android\":[{\"src\":\"\\/assets\\/img\\/default\\/android.png\",\"alt\":\"\\u7f51\\u6821\\u7684Android\\u7248APP\"}]}','\n<div class=\"col-md-8 footer-main clearfix\">\n  <div class=\"link-item \">\n  <h3>我是学生</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/673\" target=\"_blank\">如何注册</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/705\" target=\"_blank\">如何学习</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/347/learn#lesson/811\" target=\"_blank\">如何互动</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是老师</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/22\" target=\"_blank\">发布课程</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/147\" target=\"_blank\">使用题库</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/372\" target=\"_blank\">教学资料库</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item \">\n  <h3>我是管理员</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/340\" target=\"_blank\">系统设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/341\" target=\"_blank\">课程设置</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/343\" target=\"_blank\">用户管理</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>商业应用</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/358\" target=\"_blank\">会员专区</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/232/learn#lesson/467\" target=\"_blank\">题库增强版</a>\n      </li>\n          <li>\n        <a href=\"http://www.qiqiuyu.com/course/380\" target=\"_blank\">用户导入导出</a>\n      </li>\n      </ul>\n</div>\n\n  <div class=\"link-item hidden-xs\">\n  <h3>关于我们</h3>\n  <ul>\n          <li>\n        <a href=\"http://www.edusoho.com/\" target=\"_blank\">ES官网</a>\n      </li>\n          <li>\n        <a href=\"http://weibo.com/qiqiuyu/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\" target=\"_blank\">官方微博</a>\n      </li>\n          <li>\n        <a href=\"http://www.edusoho.com/abouts/joinus\" target=\"_blank\">加入我们</a>\n      </li>\n      </ul>\n</div>\n\n</div>\n\n<div class=\"col-md-4 footer-logo hidden-sm hidden-xs\">\n  <a class=\"\" href=\"http://www.edusoho.com\" target=\"_blank\"><img src=\"/assets/v2/img/bottom_logo.png?6.12.2\" alt=\"建议图片大小为233*64\"></a>\n  <div class=\"footer-sns\">\n        <a href=\"http://weibo.com/edusoho\" target=\"_blank\"><i class=\"es-icon es-icon-weibo\"></i></a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-weixin\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/weixin.png?6.12.2\" alt=\"\">  \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-apple\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/apple.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n            <a class=\"qrcode-popover top\">\n      <i class=\"es-icon es-icon-android\"></i>\n      <div class=\"qrcode-content\">\n        <img src=\"/assets/img/default/android.png?6.12.2\" alt=\"\"> \n      </div>\n    </a>\n      </div>\n</div>\n\n\n',1,1453222579),(25,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_net.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453276249),(26,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_app.jpg\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453276270),(27,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/themes/biligcc/img/banner_eweek.jpg\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453276294),(28,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453276315),(29,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"limitWide\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div class=\"container\">\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453278030),(30,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453278050),(31,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#02987c\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #02987c;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453278052),(32,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#0984f7\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #0984f7;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453280271),(33,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#3b4250\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #3b4250;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453280313),(34,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155108c32177254251.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453280346),(35,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155132410ca5833497.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453954749),(36,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-20/155153907728837252.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453954935),(37,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122222e4b3e7666048.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453954944),(38,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122222e4b3e7666048.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453954954),(39,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122222e4b3e7666048.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453954957),(40,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/1218582975f9257067.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122325d98b0e172705.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453955007),(41,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12233468c3d4630561.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12233468c3d4630561.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12221353e4dd457950.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122325d98b0e172705.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453955016),(42,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/12233468c3d4630561.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122359f4e374523128.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/12233468c3d4630561.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122359f4e374523128.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122325d98b0e172705.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453955042),(43,11,NULL,'{\"posters\":[{\"alt\":\"\\u6d77\\u62a51\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122410a88557276811.jpg?6.12.2\",\"background\":\"#ff9c00\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a52\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122359f4e374523128.jpg?6.12.2\",\"background\":\"#59b2ac\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a53\",\"status\":\"1\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\",\"background\":\"#a8e6d9\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a54\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/files\\/system\\/2016\\/01-28\\/122231791505668913.jpg?6.12.2\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a55\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a56\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a57\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"},{\"alt\":\"\\u6d77\\u62a58\",\"status\":\"0\",\"layout\":\"tile\",\"mode\":\"img\",\"src\":\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\",\"background\":\"#3ec768\",\"href\":\"\",\"html\":\"\"}]}','<section class=\"es-poster swiper-container\">\n  <div class=\"swiper-wrapper\">\n                            <div class=\"swiper-slide swiper-hidden\" style=\"background: #ff9c00;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122410a88557276811.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #59b2ac;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122359f4e374523128.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                          <div class=\"swiper-slide swiper-hidden\" style=\"background: #a8e6d9;\">\n            <div >\n              <a href=\"\" target=\"_blank\" ><img class=\"img-responsive\" src=\"/files/system/2016/01-28/122325d98b0e172705.jpg?6.12.2\">\n              </a>\n            </div>\n          </div>\n                                                                      </div>\n  <div class=\"swiper-pager\"></div>\n</section>',1,1453955052);
/*!40000 ALTER TABLE `block_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '缓存ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `data` longblob COMMENT '数据',
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否序列化',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expiredTime` (`expiredTime`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='缓存';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES (61,'settings','a:26:{s:7:\"contact\";s:326:\"a:8:{s:7:\"enabled\";i:0;s:8:\"worktime\";s:12:\"9:00 - 17:00\";s:2:\"qq\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:7:\"qqgroup\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:5:\"phone\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:10:\"webchatURI\";s:0:\"\";s:5:\"email\";s:0:\"\";s:5:\"color\";s:7:\"default\";}\";s:6:\"mailer\";s:205:\"a:7:{s:7:\"enabled\";i:1;s:4:\"host\";s:18:\"smtp.exmail.qq.com\";s:4:\"port\";s:2:\"25\";s:8:\"username\";s:16:\"test@edusoho.com\";s:8:\"password\";s:6:\"est123\";s:4:\"from\";s:16:\"test@edusoho.com\";s:4:\"name\";s:4:\"TEST\";}\";s:6:\"refund\";s:417:\"a:4:{s:13:\"maxRefundDays\";i:10;s:17:\"applyNotification\";s:107:\"您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。\";s:19:\"successNotification\";s:82:\"您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。\";s:18:\"failedNotification\";s:93:\"您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。\";}\";s:14:\"post_num_rules\";s:211:\"a:1:{s:5:\"rules\";a:2:{s:6:\"thread\";a:1:{s:14:\"fiveMuniteRule\";a:2:{s:8:\"interval\";i:300;s:7:\"postNum\";i:100;}}s:17:\"threadLoginedUser\";a:1:{s:14:\"fiveMuniteRule\";a:2:{s:8:\"interval\";i:300;s:7:\"postNum\";i:50;}}}}\";s:7:\"consult\";s:382:\"a:9:{s:7:\"enabled\";s:1:\"1\";s:5:\"color\";s:7:\"default\";s:2:\"qq\";a:1:{i:0;a:3:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";s:3:\"url\";s:0:\"\";}}s:7:\"qqgroup\";a:1:{i:0;a:3:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";s:3:\"url\";s:0:\"\";}}s:8:\"worktime\";s:12:\"9:00 - 17:00\";s:5:\"phone\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:4:\"file\";s:0:\"\";s:10:\"webchatURI\";s:0:\"\";s:5:\"email\";s:0:\"\";}\";s:12:\"user_default\";s:35:\"a:1:{s:9:\"user_name\";s:6:\"学员\";}\";s:4:\"auth\";s:1136:\"a:17:{s:13:\"register_mode\";s:5:\"email\";s:13:\"email_enabled\";s:6:\"closed\";s:12:\"setting_time\";i:1453041259;s:22:\"email_activation_title\";s:33:\"请激活您的{{sitename}}帐号\";s:21:\"email_activation_body\";s:380:\"Hi, {{nickname}}\r\n\r\n欢迎加入{{sitename}}!\r\n\r\n请点击下面的链接完成注册：\r\n\r\n{{verifyurl}}\r\n\r\n如果以上链接无法点击，请将上面的地址复制到你的浏览器(如IE)的地址栏中打开，该链接地址24小时内打开有效。\r\n\r\n感谢对{{sitename}}的支持！\r\n\r\n{{sitename}} {{siteurl}}\r\n\r\n(这是一封自动产生的email，请勿回复。)\";s:15:\"welcome_enabled\";s:6:\"opened\";s:14:\"welcome_sender\";s:15:\"测试管理员\";s:15:\"welcome_methods\";a:0:{}s:13:\"welcome_title\";s:24:\"欢迎加入{{sitename}}\";s:12:\"welcome_body\";s:138:\"您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。\";s:10:\"user_terms\";s:6:\"opened\";s:15:\"user_terms_body\";s:0:\"\";s:15:\"captcha_enabled\";i:0;s:19:\"register_protective\";s:4:\"none\";s:16:\"nickname_enabled\";i:0;s:12:\"avatar_alert\";s:4:\"none\";s:10:\"_cloud_sms\";s:0:\"\";}\";s:7:\"storage\";s:215:\"a:6:{s:11:\"upload_mode\";s:5:\"local\";s:16:\"cloud_api_server\";s:22:\"http://api.edusoho.net\";s:16:\"cloud_access_key\";s:0:\"\";s:12:\"cloud_bucket\";s:0:\"\";s:16:\"cloud_secret_key\";s:0:\"\";s:20:\"cloud_api_tui_server\";s:0:\"\";}\";s:9:\"developer\";s:231:\"a:7:{s:5:\"debug\";s:1:\"1\";s:11:\"app_api_url\";s:0:\"\";s:16:\"cloud_api_server\";s:22:\"http://api.edusoho.net\";s:20:\"cloud_api_tui_server\";s:0:\"\";s:13:\"hls_encrypted\";s:1:\"1\";s:14:\"balloon_player\";s:1:\"1\";s:15:\"without_network\";s:1:\"0\";}\";s:10:\"login_bind\";s:1132:\"a:27:{s:11:\"login_limit\";s:1:\"1\";s:7:\"enabled\";s:1:\"1\";s:22:\"temporary_lock_enabled\";s:1:\"1\";s:28:\"temporary_lock_allowed_times\";s:1:\"5\";s:31:\"ip_temporary_lock_allowed_times\";s:2:\"20\";s:22:\"temporary_lock_minutes\";s:2:\"20\";s:13:\"weibo_enabled\";s:1:\"1\";s:9:\"weibo_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:12:\"weibo_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:22:\"weibo_set_fill_account\";s:1:\"1\";s:10:\"qq_enabled\";s:1:\"1\";s:6:\"qq_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:9:\"qq_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:19:\"qq_set_fill_account\";s:1:\"1\";s:14:\"renren_enabled\";s:1:\"1\";s:10:\"renren_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"renren_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:23:\"renren_set_fill_account\";s:1:\"1\";s:17:\"weixinweb_enabled\";s:1:\"1\";s:13:\"weixinweb_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"weixinweb_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:26:\"weixinweb_set_fill_account\";s:1:\"1\";s:17:\"weixinmob_enabled\";s:1:\"1\";s:13:\"weixinmob_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"weixinmob_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:26:\"weixinmob_set_fill_account\";s:1:\"1\";s:11:\"verify_code\";s:0:\"\";}\";s:14:\"course_default\";s:62:\"a:2:{s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";}\";s:7:\"default\";s:91:\"a:3:{s:9:\"user_name\";s:6:\"学员\";s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";}\";s:12:\"menu_hiddens\";s:6:\"a:0:{}\";s:7:\"payment\";s:869:\"a:19:{s:7:\"enabled\";s:1:\"1\";s:16:\"disabled_message\";s:48:\"尚未开启支付模块，无法购买课程。\";s:14:\"alipay_enabled\";s:1:\"1\";s:11:\"alipay_type\";s:6:\"direct\";s:10:\"alipay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"alipay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:14:\"alipay_account\";s:17:\"torghay@bilig.biz\";s:19:\"close_trade_enabled\";s:1:\"1\";s:13:\"wxpay_enabled\";s:1:\"1\";s:9:\"wxpay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"wxpay_account\";s:13:\"dsdasdasdasda\";s:12:\"wxpay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:14:\"heepay_enabled\";s:1:\"1\";s:10:\"heepay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"heepay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"quickpay_enabled\";s:1:\"1\";s:12:\"quickpay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:15:\"quickpay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:12:\"quickpay_aes\";s:26:\"ddsdsadasdasdasdasaddasdas\";}\";s:16:\"operation_mobile\";s:6:\"a:0:{}\";s:22:\"operation_course_grids\";s:6:\"a:0:{}\";s:6:\"mobile\";s:209:\"a:10:{s:7:\"enabled\";s:1:\"1\";s:3:\"ver\";s:1:\"1\";s:5:\"about\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:6:\"notice\";s:0:\"\";s:7:\"splash1\";s:0:\"\";s:7:\"splash2\";s:0:\"\";s:7:\"splash3\";s:0:\"\";s:7:\"splash4\";s:0:\"\";s:7:\"splash5\";s:0:\"\";}\";s:5:\"esBar\";s:28:\"a:1:{s:7:\"enabled\";s:1:\"1\";}\";s:5:\"theme\";s:232:\"a:8:{s:4:\"code\";s:6:\"jianmo\";s:4:\"name\";s:6:\"简墨\";s:6:\"author\";s:13:\"EduSoho官方\";s:7:\"version\";s:5:\"1.0.0\";s:15:\"supprot_version\";s:6:\"6.0.0+\";s:4:\"date\";s:8:\"2015-6-1\";s:5:\"thumb\";s:13:\"img/theme.jpg\";s:3:\"uri\";s:6:\"jianmo\";}\";s:4:\"site\";s:665:\"a:14:{s:4:\"name\";s:38:\"بىلىگ تور دەرسخانىسى\";s:6:\"slogan\";s:33:\"强大的在线教育解决方案\";s:3:\"url\";s:23:\"http://demo.edusoho.com\";s:4:\"file\";s:0:\"\";s:4:\"logo\";s:46:\"files/system/2016/01-28/120743fc8304922262.png\";s:7:\"favicon\";s:46:\"files/system/2016/01-28/1221426c3d56265089.png\";s:12:\"seo_keywords\";s:59:\"edusoho, 在线教育软件, 在线在线教育解决方案\";s:15:\"seo_description\";s:43:\"edusoho是强大的在线教育开源软件\";s:12:\"master_email\";s:16:\"test@edusoho.com\";s:9:\"copyright\";s:12:\"必利网络\";s:3:\"icp\";s:23:\" 浙ICP备13006852号-1\";s:9:\"analytics\";s:0:\"\";s:6:\"status\";s:4:\"open\";s:11:\"closed_note\";s:0:\"\";}\";s:9:\"classroom\";s:74:\"a:3:{s:7:\"enabled\";s:1:\"1\";s:4:\"name\";s:0:\"\";s:12:\"discount_buy\";s:1:\"1\";}\";s:6:\"invite\";s:189:\"a:5:{s:19:\"invite_code_setting\";s:1:\"1\";s:19:\"promoted_user_value\";s:0:\"\";s:18:\"promote_user_value\";s:0:\"\";s:8:\"deadline\";s:2:\"90\";s:25:\"inviteInfomation_template\";s:16:\"{{registerUrl}} \";}\";s:6:\"course\";s:751:\"a:20:{s:23:\"welcome_message_enabled\";s:1:\"1\";s:20:\"welcome_message_body\";s:41:\"{{nickname}},欢迎加入课程{{course}}\";s:20:\"teacher_modify_price\";s:1:\"1\";s:20:\"teacher_search_order\";s:1:\"1\";s:22:\"teacher_manage_student\";s:1:\"1\";s:22:\"teacher_export_student\";s:1:\"0\";s:22:\"student_download_media\";s:1:\"0\";s:23:\"explore_default_orderBy\";s:6:\"latest\";s:14:\"relatedCourses\";s:1:\"1\";s:21:\"allowAnonymousPreview\";s:1:\"1\";s:12:\"copy_enabled\";s:1:\"1\";s:21:\"testpaperCopy_enabled\";s:1:\"1\";s:24:\"show_student_num_enabled\";s:1:\"1\";s:22:\"custom_chapter_enabled\";s:1:\"1\";s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";s:14:\"userinfoFields\";a:0:{}s:22:\"userinfoFieldNameArray\";a:0:{}s:19:\"live_course_enabled\";s:1:\"1\";s:21:\"live_student_capacity\";i:0;}\";s:11:\"live-course\";s:74:\"a:2:{s:19:\"live_course_enabled\";s:1:\"1\";s:21:\"live_student_capacity\";i:0;}\";s:4:\"coin\";s:347:\"a:11:{s:9:\"coin_name\";s:9:\"虚拟币\";s:12:\"coin_picture\";s:0:\"\";s:18:\"coin_picture_50_50\";s:0:\"\";s:18:\"coin_picture_30_30\";s:0:\"\";s:18:\"coin_picture_20_20\";s:0:\"\";s:18:\"coin_picture_10_10\";s:0:\"\";s:9:\"cash_rate\";s:2:\"10\";s:12:\"coin_enabled\";s:1:\"1\";s:10:\"cash_model\";s:9:\"deduction\";s:19:\"charge_coin_enabled\";s:1:\"0\";s:12:\"coin_content\";s:0:\"\";}\";s:15:\"_app_last_check\";s:13:\"i:1455419022;\";}',1,0,1455419022);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card`
--

DROP TABLE IF EXISTS `card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cardId` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的ID',
  `cardType` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的类型',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
  `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `status` enum('used','receive','invalid','deleted') NOT NULL DEFAULT 'receive' COMMENT '使用状态',
  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '领取时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card`
--

LOCK TABLES `card` WRITE;
/*!40000 ALTER TABLE `card` DISABLE KEYS */;
/*!40000 ALTER TABLE `card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_account`
--

DROP TABLE IF EXISTS `cash_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `cash` float(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_account`
--

LOCK TABLES `cash_account` WRITE;
/*!40000 ALTER TABLE `cash_account` DISABLE KEYS */;
INSERT INTO `cash_account` VALUES (1,1,0.00);
/*!40000 ALTER TABLE `cash_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_change`
--

DROP TABLE IF EXISTS `cash_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_change` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `amount` double(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_change`
--

LOCK TABLES `cash_change` WRITE;
/*!40000 ALTER TABLE `cash_change` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_change` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_flow`
--

DROP TABLE IF EXISTS `cash_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
  `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
  `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `cashType` enum('RMB','Coin') NOT NULL DEFAULT 'Coin',
  `parentSn` bigint(20) DEFAULT NULL,
  `cash` float(10,2) NOT NULL DEFAULT '0.00',
  `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
  `orderSn` varchar(40) NOT NULL COMMENT '订单号',
  `category` varchar(128) NOT NULL DEFAULT '' COMMENT '帐目类目',
  `payment` enum('alipay','wxpay','heepay','quickpay','iosiap') DEFAULT NULL,
  `note` text COMMENT '备注',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tradeNo` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_flow`
--

LOCK TABLES `cash_flow` WRITE;
/*!40000 ALTER TABLE `cash_flow` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_flow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_orders`
--

DROP TABLE IF EXISTS `cash_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(32) NOT NULL COMMENT '订单号',
  `status` enum('created','paid','cancelled') NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00',
  `payment` enum('none','alipay','wxpay','heepay','quickpay','iosiap') NOT NULL,
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `targetType` varchar(64) NOT NULL DEFAULT 'coin' COMMENT '订单类型',
  `token` varchar(50) DEFAULT NULL COMMENT '令牌',
  `data` text COMMENT '订单业务数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_orders`
--

LOCK TABLES `cash_orders` WRITE;
/*!40000 ALTER TABLE `cash_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_orders_log`
--

DROP TABLE IF EXISTS `cash_orders_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_orders_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `message` text,
  `data` text,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_orders_log`
--

LOCK TABLES `cash_orders_log` WRITE;
/*!40000 ALTER TABLE `cash_orders_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_orders_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '编码',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `groupId` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (2,'default','دەرسلىكلەر','','',100,2,0,''),(3,'php','تىل دەرسلىكلىرى','','',0,2,0,''),(4,'java','تىل ئۆگۈنۈش','','',0,2,2,'');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_group`
--

DROP TABLE IF EXISTS `category_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_group`
--

LOCK TABLES `category_group` WRITE;
/*!40000 ALTER TABLE `category_group` DISABLE KEYS */;
INSERT INTO `category_group` VALUES (2,'course','课程分类',2);
/*!40000 ALTER TABLE `category_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classroom`
--

DROP TABLE IF EXISTS `classroom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
  `about` text COMMENT '简介',
  `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类id',
  `description` text COMMENT '课程说明',
  `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
  `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
  `headTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班主任ID',
  `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs',
  `assistantIds` text COMMENT '助教Ids',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `auditorNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '旁听生数',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
  `courseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程数',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级笔记数量',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '排行数值',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '收入',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `service` varchar(255) DEFAULT NULL COMMENT '班级服务',
  `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级',
  `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示',
  `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买',
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classroom`
--

LOCK TABLES `classroom` WRITE;
/*!40000 ALTER TABLE `classroom` DISABLE KEYS */;
/*!40000 ALTER TABLE `classroom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classroom_courses`
--

DROP TABLE IF EXISTS `classroom_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classroomId` int(10) unsigned NOT NULL COMMENT '班级ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
  `parentCourseId` int(10) unsigned NOT NULL COMMENT '父课程Id',
  `seq` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级课程顺序',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classroom_courses`
--

LOCK TABLES `classroom_courses` WRITE;
/*!40000 ALTER TABLE `classroom_courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `classroom_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classroom_member`
--

DROP TABLE IF EXISTS `classroom_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
  `remark` text COMMENT '备注',
  `role` varchar(255) NOT NULL DEFAULT 'auditor' COMMENT '角色',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classroom_member`
--

LOCK TABLES `classroom_member` WRITE;
/*!40000 ALTER TABLE `classroom_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `classroom_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classroom_review`
--

DROP TABLE IF EXISTS `classroom_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分0-5',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classroom_review`
--

LOCK TABLES `classroom_review` WRITE;
/*!40000 ALTER TABLE `classroom_review` DISABLE KEYS */;
/*!40000 ALTER TABLE `classroom_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cloud_app`
--

DROP TABLE IF EXISTS `cloud_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cloud_app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `code` varchar(255) NOT NULL COMMENT '编码',
  `type` enum('plugin','theme') NOT NULL DEFAULT 'plugin' COMMENT '应用类型(plugin插件应用, theme主题应用)',
  `description` text NOT NULL COMMENT '描述',
  `icon` varchar(255) NOT NULL COMMENT '图标',
  `version` varchar(32) NOT NULL COMMENT '当前版本',
  `fromVersion` varchar(32) NOT NULL DEFAULT '0.0.0' COMMENT '更新前版本',
  `developerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开发者用户ID',
  `developerName` varchar(255) NOT NULL DEFAULT '' COMMENT '开发者名称',
  `installedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='已安装的应用';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cloud_app`
--

LOCK TABLES `cloud_app` WRITE;
/*!40000 ALTER TABLE `cloud_app` DISABLE KEYS */;
INSERT INTO `cloud_app` VALUES (1,'EduSoho主系统','MAIN','plugin','EduSoho主系统','','6.12.2','0.0.0',1,'EduSoho官方',1453041118,1453041118),(2,'必利','bilig','theme','','','1.0.0','1.0.0',0,'未知',1453215900,1453215900),(3,'必利','biligcc','theme','','','1.0.0','1.0.0',0,'未知',1453222578,1453222578);
/*!40000 ALTER TABLE `cloud_app` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cloud_app_logs`
--

DROP TABLE IF EXISTS `cloud_app_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cloud_app_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '' COMMENT '应用编码',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '应用名称',
  `fromVersion` varchar(32) DEFAULT '' COMMENT '升级前版本',
  `toVersion` varchar(32) NOT NULL DEFAULT '' COMMENT '升级后版本',
  `type` enum('install','upgrade') NOT NULL DEFAULT 'install' COMMENT '升级类型',
  `dbBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '数据库备份文件',
  `sourceBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '源文件备份地址',
  `status` varchar(32) NOT NULL COMMENT '升级状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '升级时的IP',
  `message` text COMMENT '失败原因',
  `createdTime` int(10) unsigned NOT NULL COMMENT '日志记录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用升级日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cloud_app_logs`
--

LOCK TABLES `cloud_app_logs` WRITE;
/*!40000 ALTER TABLE `cloud_app_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cloud_app_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(32) NOT NULL,
  `objectId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objectType` (`objectType`,`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `editor` enum('richeditor','none') NOT NULL DEFAULT 'richeditor' COMMENT '编辑器选择类型字段',
  `type` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `summary` text,
  `body` text,
  `picture` varchar(255) NOT NULL DEFAULT '',
  `template` varchar(255) NOT NULL DEFAULT '',
  `status` enum('published','unpublished','trash') NOT NULL,
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tagIds` tinytext,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` int(10) unsigned NOT NULL DEFAULT '0',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '在列表中是否显示该条目。',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `userId` int(10) unsigned NOT NULL,
  `field1` text,
  `field2` text,
  `field3` text,
  `field4` text,
  `field5` text,
  `field6` text,
  `field7` text,
  `field8` text,
  `field9` text,
  `field10` text,
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon`
--

DROP TABLE IF EXISTS `coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL COMMENT '优惠码',
  `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
  `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态',
  `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
  `batchId` int(10) unsigned DEFAULT NULL COMMENT '批次号',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
  `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
  `targetType` varchar(64) DEFAULT NULL COMMENT '使用对象类型',
  `targetId` int(10) unsigned DEFAULT '0' COMMENT '使用对象',
  `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `orderTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `createdTime` int(10) unsigned NOT NULL,
  `receiveTime` int(10) DEFAULT '0' COMMENT '接收时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon`
--

LOCK TABLES `coupon` WRITE;
/*!40000 ALTER TABLE `coupon` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_batch`
--

DROP TABLE IF EXISTS `coupon_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT '批次名称',
  `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
  `generatedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生成数',
  `usedNum` int(11) NOT NULL DEFAULT '0' COMMENT '使用次数',
  `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
  `prefix` varchar(64) NOT NULL COMMENT '批次前缀',
  `digits` int(20) unsigned NOT NULL COMMENT '优惠码位数',
  `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已优惠金额',
  `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '使用对象类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text COMMENT '优惠说明',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码批次表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_batch`
--

LOCK TABLES `coupon_batch` WRITE;
/*!40000 ALTER TABLE `coupon_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL,
  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '副标题',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
  `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买',
  `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
  `maxStudentNum` int(11) NOT NULL DEFAULT '0' COMMENT '直播课程最大学员数上线',
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `originPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `coinPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `expiryDay` int(10) unsigned NOT NULL DEFAULT '0',
  `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened',
  `serializeMode` enum('none','serialize','finished') NOT NULL DEFAULT 'none',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程销售总收入',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0',
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '排行数值',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以免费看的，会员等级',
  `useInClassroom` enum('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级',
  `singleBuy` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tags` text,
  `smallPicture` varchar(255) NOT NULL DEFAULT '',
  `middlePicture` varchar(255) NOT NULL DEFAULT '',
  `largePicture` varchar(255) NOT NULL DEFAULT '',
  `about` text,
  `teacherIds` text,
  `goals` text COMMENT '课程目标',
  `audiences` text COMMENT 'audiences',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的父Id',
  `address` varchar(255) NOT NULL DEFAULT '',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数量',
  `userId` int(10) unsigned NOT NULL,
  `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知',
  `daysOfNotifyBeforeDeadline` int(10) NOT NULL DEFAULT '0',
  `watchLimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时观看次数限制',
  `createdTime` int(10) unsigned NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `freeStartTime` int(10) NOT NULL DEFAULT '0',
  `freeEndTime` int(10) NOT NULL DEFAULT '0',
  `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID',
  `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣',
  `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名认证',
  `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  `tryLookable` tinyint(4) NOT NULL DEFAULT '0',
  `tryLookTime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course`
--

LOCK TABLES `course` WRITE;
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` VALUES (1,'بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى','بىلىگ تور دەرسخانىسى ئېكراننى سۈرەتكە ئېلىش دەسلىكى، دەرسلىك ئۈلگىسى','published',1,'normal',0,3.00,3.00,0.00,0.00,30,'opened','serialize',0.00,0,0,5,1,0,'single',1,2,'|1|','public://default/2016/01-28/1226488b0101931493.jpg','public://default/2016/01-28/1226488af4f1156285.jpg','public://default/2016/01-28/1226488ae2a7205388.jpg','<p style=\"text-align:right;\">بىلىگ تور دەسخانىسى قۇرىلغاندىن بۇيانقى ئۈچ يىل جەريانىدا ئۈزلۈكسىز يېڭىلاش نەتىجىسىدە ئۇيغۇرلاردىكى ئەڭ مۇكەممەل سېستىمىغا ئىگە تور دەسخانىسىنى شەكىللەندۈردى. يېقىندا دەرسخانىمىز تورداشلارنىڭ كۈچلۈك تەلىپىنى قاندۇرۇش مەقسىتىدە، بىرقىسىم تىل دەرسلىكى، كەسپىي ئۆگۈنۈش دەسلىكى قاتارلىق دەرسلىكلەرنى سىنغا ئېلىپ قويۇش ئۈچۈن بۇ جەھەتتە ئالاھىدىلىكى بولغان ئىقتىساسلىقلاردىن ئوقۇتقۇچى قوبۇل قىلماقچى.</p>\n\n<p style=\"text-align:right;\">ھېچقانداق ۋاقىت، رايون چەكلىمىسىگە ئۇچىرىمايسىز، مەيلى سىزنىڭ قانداقلا ۋاقىتتا بوش ۋاقتىڭىز بولسۇن بىمالال دەرسلىك ئىشلىيەلەيسز. مۇكەممەل ئىقتىدارلىق تور دەسخانىسى پەقەت سىزنىڭ سىنىپ مۇنبىرىدىكى ئورنىڭىزنى تورغا يۆتكىشىڭىزنىلا كۈتۈپ تۇرماقتا. بىلىمىڭىز ئارقىلىق ھېچقانداق چەكلىمىسىز، ھېچقانداق چىقىمسىز ئىقتىسادىي قىممەت يارتىشنى خالامسىز؟</p>\n','|1|','','',0,0,0,0,0,'',0,8,0,1,'none',0,0,1453213756,1453955208,0,0,0,10.00,0,0,100,0,0),(2,'Java ئاساسىي بىلىم دەرسلىكى','Java ئاساسىي بىلىم دەرسلىكى, Java ئاساسىي بىلىم دەرسلىكى.','published',1,'normal',0,0.00,0.00,0.00,0.00,10,'opened','finished',0.00,0,0,5,1,0,'single',1,2,'|1|','public://default/2016/01-28/1226179372c5216983.jpg','public://default/2016/01-28/1226179368ee062619.jpg','public://default/2016/01-28/1226179349cd199687.jpg','','|1|',NULL,NULL,0,0,0,0,0,'',0,8,0,1,'none',0,0,1453221397,1453955177,0,0,0,10.00,0,0,100,0,0),(3,'بىلىگ دەرسخانىسى','بىلىگ تور دەرسخانىسىنى ئىشلىتىش توغرىسىدا','published',1,'normal',0,0.00,0.00,0.00,0.00,0,'opened','none',0.00,0,0,0,0,0,'single',1,0,'|3|','public://default/2016/01-28/12255319a227809425.jpg','public://default/2016/01-28/122553199627737240.jpg','public://default/2016/01-28/122553198492933043.jpg','','|1|',NULL,NULL,0,0,0,0,0,'',0,6,0,1,'none',0,0,1453265572,1453955153,0,0,0,10.00,0,0,100,0,0),(4,'تېز سۈرەتتە توربېكەت قۇرۇش','','published',1,'normal',0,0.90,0.90,0.00,0.00,0,'opened','none',0.00,0,0,0,0,0,'single',1,0,'','public://default/2016/01-28/122517d69a0d768881.jpg','public://default/2016/01-28/122517d69048126646.jpg','public://default/2016/01-28/122517d68574758387.jpg','','|1|',NULL,NULL,0,0,0,0,0,'',0,2,0,1,'none',0,0,1453265889,1453955117,0,0,0,10.00,0,0,100,0,0);
/*!40000 ALTER TABLE `course` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_chapter`
--

DROP TABLE IF EXISTS `course_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `type` enum('chapter','unit') NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元。',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'parentId大于０时为单元',
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_chapter`
--

LOCK TABLES `course_chapter` WRITE;
/*!40000 ALTER TABLE `course_chapter` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_chapter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_draft`
--

DROP TABLE IF EXISTS `course_draft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_draft` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `summary` text,
  `courseId` int(10) unsigned NOT NULL,
  `content` text,
  `userId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_draft`
--

LOCK TABLES `course_draft` WRITE;
/*!40000 ALTER TABLE `course_draft` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_draft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_favorite`
--

DROP TABLE IF EXISTS `course_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏的id',
  `courseId` int(10) unsigned NOT NULL COMMENT '收藏课程的Id',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人的Id',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_favorite`
--

LOCK TABLES `course_favorite` WRITE;
/*!40000 ALTER TABLE `course_favorite` DISABLE KEYS */;
INSERT INTO `course_favorite` VALUES (1,2,1,1453264421),(2,3,1,1453276497);
/*!40000 ALTER TABLE `course_favorite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_lesson`
--

DROP TABLE IF EXISTS `course_lesson`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `chapterId` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `free` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'published',
  `title` varchar(255) NOT NULL,
  `summary` text,
  `tags` text,
  `type` varchar(64) NOT NULL DEFAULT 'text',
  `content` text,
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课时获得的学分',
  `requireCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习课时前，需达到的学分',
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID(user_disk_file.id)',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
  `mediaUri` text COMMENT '媒体文件资源名',
  `homeworkId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作业iD',
  `exerciseId` int(10) unsigned DEFAULT '0' COMMENT '练习ID',
  `length` int(11) unsigned DEFAULT NULL,
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时结束时间',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时加入人数',
  `replayStatus` enum('ungenerated','generating','generated') NOT NULL DEFAULT 'ungenerated',
  `maxOnlineNum` int(11) DEFAULT '0' COMMENT '直播峰值在线人数',
  `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
  `testMode` enum('normal','realTime') DEFAULT 'normal' COMMENT '考试模式',
  `testStartTime` int(10) DEFAULT '0' COMMENT '实时考试开始时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_lesson`
--

LOCK TABLES `course_lesson` WRITE;
/*!40000 ALTER TABLE `course_lesson` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_lesson` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_lesson_learn`
--

DROP TABLE IF EXISTS `course_lesson_learn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `status` enum('learning','finished') NOT NULL,
  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `learnTime` int(10) unsigned NOT NULL DEFAULT '0',
  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
  `watchNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时已观看次数',
  `videoStatus` enum('paused','playing') NOT NULL DEFAULT 'paused',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_lessonId` (`userId`,`lessonId`),
  KEY `userId_courseId` (`userId`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_lesson_learn`
--

LOCK TABLES `course_lesson_learn` WRITE;
/*!40000 ALTER TABLE `course_lesson_learn` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_lesson_learn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_lesson_replay`
--

DROP TABLE IF EXISTS `course_lesson_replay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_replay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lessonId` int(10) unsigned NOT NULL COMMENT '所属课时',
  `courseId` int(10) unsigned NOT NULL COMMENT '所属课程',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `replayId` text NOT NULL COMMENT '云直播中的回放id',
  `userId` int(10) unsigned NOT NULL COMMENT '创建者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `hidden` tinyint(1) unsigned DEFAULT '0' COMMENT '观看状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_lesson_replay`
--

LOCK TABLES `course_lesson_replay` WRITE;
/*!40000 ALTER TABLE `course_lesson_replay` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_lesson_replay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_lesson_view`
--

DROP TABLE IF EXISTS `course_lesson_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_view` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) NOT NULL,
  `lessonId` int(10) NOT NULL,
  `fileId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `fileType` enum('document','video','audio','image','ppt','other','none') NOT NULL DEFAULT 'none',
  `fileStorage` enum('local','cloud','net','none') NOT NULL,
  `fileSource` varchar(32) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_lesson_view`
--

LOCK TABLES `course_lesson_view` WRITE;
/*!40000 ALTER TABLE `course_lesson_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_lesson_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_material`
--

DROP TABLE IF EXISTS `course_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `description` text,
  `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
  `fileId` int(10) unsigned NOT NULL,
  `fileUri` varchar(255) NOT NULL DEFAULT '',
  `fileMime` varchar(255) NOT NULL DEFAULT '',
  `fileSize` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_material`
--

LOCK TABLES `course_material` WRITE;
/*!40000 ALTER TABLE `course_material` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_member`
--

DROP TABLE IF EXISTS `course_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `classroomId` int(10) NOT NULL DEFAULT '0' COMMENT '班级ID',
  `joinedType` enum('course','classroom') NOT NULL DEFAULT 'course' COMMENT '购买班级或者课程加入学习',
  `userId` int(10) unsigned NOT NULL,
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员购买课程时的订单ID',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0',
  `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户以会员的方式加入课程学员时的会员ID',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员已获得的学分',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数目',
  `noteLastUpdateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新的笔记更新时间',
  `isLearned` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seq` int(10) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
  `role` enum('student','teacher') NOT NULL DEFAULT 'student',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `deadlineNotified` int(10) NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courseId` (`courseId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_member`
--

LOCK TABLES `course_member` WRITE;
/*!40000 ALTER TABLE `course_member` DISABLE KEYS */;
INSERT INTO `course_member` VALUES (1,1,0,'course',1,0,0,0,0,0,0,0,0,0,'',1,'teacher',0,0,1453213756),(2,2,0,'course',1,0,0,0,0,0,0,0,0,0,'',1,'teacher',0,0,1453221397),(3,3,0,'course',1,0,0,0,0,0,0,0,0,0,'',1,'teacher',0,0,1453265572),(4,4,0,'course',1,0,0,0,0,0,0,0,0,0,'',1,'teacher',0,0,1453265889);
/*!40000 ALTER TABLE `course_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_note`
--

DROP TABLE IF EXISTS `course_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_note` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '笔记作者ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时ID',
  `content` text NOT NULL COMMENT '笔记内容',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记内容的字数',
  `likeNum` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '点赞人数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
  `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_note`
--

LOCK TABLES `course_note` WRITE;
/*!40000 ALTER TABLE `course_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_note_like`
--

DROP TABLE IF EXISTS `course_note_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_note_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noteId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_note_like`
--

LOCK TABLES `course_note_like` WRITE;
/*!40000 ALTER TABLE `course_note_like` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_note_like` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_review`
--

DROP TABLE IF EXISTS `course_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '评论title',
  `content` text NOT NULL COMMENT '评论内容',
  `rating` int(10) unsigned NOT NULL DEFAULT '0',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否私有',
  `createdTime` int(10) unsigned NOT NULL COMMENT '评价创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_review`
--

LOCK TABLES `course_review` WRITE;
/*!40000 ALTER TABLE `course_review` DISABLE KEYS */;
INSERT INTO `course_review` VALUES (1,1,2,'','بەك ياخشى دەرسلىك ',5,0,1453264380),(2,1,1,'','سەرخىل دەرسلىكلەر بېكىتىمىزدە',5,0,1453264573);
/*!40000 ALTER TABLE `course_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_thread`
--

DROP TABLE IF EXISTS `course_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('discussion','question') NOT NULL DEFAULT 'discussion',
  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `isClosed` int(10) unsigned NOT NULL DEFAULT '0',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否私有',
  `title` varchar(255) NOT NULL,
  `content` text,
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击查看的次数',
  `followNum` int(10) unsigned NOT NULL DEFAULT '0',
  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_thread`
--

LOCK TABLES `course_thread` WRITE;
/*!40000 ALTER TABLE `course_thread` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_thread_post`
--

DROP TABLE IF EXISTS `course_thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `threadId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `isElite` tinyint(4) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_thread_post`
--

LOCK TABLES `course_thread_post` WRITE;
/*!40000 ALTER TABLE `course_thread_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_thread_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crontab_job`
--

DROP TABLE IF EXISTS `crontab_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crontab_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(1024) NOT NULL COMMENT '任务名称',
  `cycle` enum('once','everyhour','everyday','everymonth') NOT NULL DEFAULT 'once' COMMENT '任务执行周期',
  `cycleTime` varchar(255) NOT NULL DEFAULT '0' COMMENT '任务执行时间',
  `jobClass` varchar(1024) NOT NULL COMMENT '任务的Class名称',
  `jobParams` text NOT NULL COMMENT '任务参数',
  `targetType` varchar(64) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `executing` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行状态',
  `nextExcutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
  `latestExecutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后执行的时间',
  `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crontab_job`
--

LOCK TABLES `crontab_job` WRITE;
/*!40000 ALTER TABLE `crontab_job` DISABLE KEYS */;
INSERT INTO `crontab_job` VALUES (1,'CancelOrderJob','everyhour','0','Topxia\\Service\\Order\\Job\\CancelOrderJob','','',0,0,1455422621,1455419021,0,0),(2,'DeleteExpiredTokenJob','everyhour','0','Topxia\\Service\\User\\Job\\DeleteExpiredTokenJob','','',0,0,1455422293,1455418693,0,0);
/*!40000 ALTER TABLE `crontab_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `uri` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file`
--

LOCK TABLES `file` WRITE;
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
INSERT INTO `file` VALUES (12,18,1,'public://default/2016/01-20/122903f48c29772900.jpg','',59933,0,1453264143),(18,18,1,'public://default/2016/01-20/12401714e1f4230426.jpg','',18153,0,1453264817),(19,23,1,'public://article/2016/01-20/1240171693a8817199.jpg','',170089,0,1453264817),(20,24,1,'public://tmp/2016/01-20/12433130b442715856.jpg','',170089,0,1453265011),(21,24,1,'public://tmp/2016/01-20/124341d77a04487117.jpg','',170089,0,1453265021),(23,18,1,'public://default/2016/01-20/12440114a873414150.jpg','',18158,0,1453265041),(24,23,1,'public://article/2016/01-20/12440116a252358093.jpg','',170089,0,1453265041),(26,18,1,'public://default/2016/01-20/1244480e2d66175177.jpg','',18968,0,1453265088),(27,23,1,'public://article/2016/01-20/12444910a64b862679.jpg','',273085,0,1453265089),(28,25,1,'public://system/2016/01-20/1248535a5546026241.png','',20522,0,1453265333),(29,24,1,'public://tmp/2016/01-20/1253255d4be8750185.jpg','',224506,0,1453265605),(47,25,1,'public://system/2016/01-20/155108c32177254251.jpg','',224506,0,1453276268),(48,25,1,'public://system/2016/01-20/155132410ca5833497.jpg','',147210,0,1453276292),(49,25,1,'public://system/2016/01-20/155153907728837252.jpg','',170520,0,1453276313),(50,25,1,'public://system/2016/01-28/120315364bd7905621.png','',20522,0,1453953795),(51,25,1,'public://system/2016/01-28/120322a6c427023260.png','',20522,0,1453953802),(52,25,1,'public://system/2016/01-28/120343f08ab0237104.png','',20522,0,1453953823),(53,25,1,'public://system/2016/01-28/120743fc8304922262.png','',20522,0,1453954063),(54,25,1,'public://system/2016/01-28/1218582975f9257067.jpg','',170089,0,1453954738),(55,25,1,'public://system/2016/01-28/1221426c3d56265089.png','',20522,0,1453954902),(56,25,1,'public://system/2016/01-28/12221353e4dd457950.jpg','',170520,0,1453954933),(57,25,1,'public://system/2016/01-28/122222e4b3e7666048.jpg','',147210,0,1453954942),(58,25,1,'public://system/2016/01-28/122231791505668913.jpg','',273085,0,1453954951),(59,25,1,'public://system/2016/01-28/122325d98b0e172705.jpg','',170520,0,1453955005),(60,25,1,'public://system/2016/01-28/12233468c3d4630561.jpg','',147210,0,1453955014),(61,25,1,'public://system/2016/01-28/122359f4e374523128.jpg','',147210,0,1453955039),(62,25,1,'public://system/2016/01-28/122410a88557276811.jpg','',273085,0,1453955050),(64,18,1,'public://default/2016/01-28/122517d68574758387.jpg','',64209,0,1453955117),(65,18,1,'public://default/2016/01-28/122517d69048126646.jpg','',28845,0,1453955117),(66,18,1,'public://default/2016/01-28/122517d69a0d768881.jpg','',4692,0,1453955117),(67,24,1,'public://tmp/2016/01-28/12254265cd8f471925.jpg','',64631,0,1453955142),(69,18,1,'public://default/2016/01-28/122553198492933043.jpg','',72102,0,1453955153),(70,18,1,'public://default/2016/01-28/122553199627737240.jpg','',34449,0,1453955153),(71,18,1,'public://default/2016/01-28/12255319a227809425.jpg','',6500,0,1453955153),(73,18,1,'public://default/2016/01-28/1226179349cd199687.jpg','',79552,0,1453955177),(74,18,1,'public://default/2016/01-28/1226179368ee062619.jpg','',38418,0,1453955177),(75,18,1,'public://default/2016/01-28/1226179372c5216983.jpg','',6776,0,1453955177),(77,18,1,'public://default/2016/01-28/1226488ae2a7205388.jpg','',64062,0,1453955208),(78,18,1,'public://default/2016/01-28/1226488af4f1156285.jpg','',29382,0,1453955208),(79,18,1,'public://default/2016/01-28/1226488b0101931493.jpg','',5429,0,1453955208),(81,18,1,'public://default/2016/01-28/122705934fb4341002.jpg','',25970,0,1453955225),(82,18,1,'public://default/2016/01-28/122705936185051189.jpg','',11744,0,1453955225),(83,18,1,'public://default/2016/01-28/122705936fd0862024.jpg','',3110,0,1453955225),(85,18,1,'public://default/2016/01-28/122748467dd9830671.jpg','',19210,0,1453955268),(87,18,1,'public://default/2016/01-28/122815f1f6dd744960.jpg','',27590,0,1453955295),(89,18,1,'public://default/2016/01-28/12283869f137651110.jpg','',21444,0,1453955318);
/*!40000 ALTER TABLE `file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_group`
--

DROP TABLE IF EXISTS `file_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `public` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_group`
--

LOCK TABLES `file_group` WRITE;
/*!40000 ALTER TABLE `file_group` DISABLE KEYS */;
INSERT INTO `file_group` VALUES (18,'默认文件组','default',1),(19,'缩略图','thumb',1),(20,'课程','course',1),(21,'用户','user',1),(22,'课程私有文件','course_private',0),(23,'资讯','article',1),(24,'临时目录','tmp',1),(25,'全局设置文件','system',1),(26,'小组','group',1),(27,'编辑区','block',1),(28,'班级','classroom',1);
/*!40000 ALTER TABLE `file_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friend`
--

DROP TABLE IF EXISTS `friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromId` int(10) unsigned NOT NULL,
  `toId` int(10) unsigned NOT NULL,
  `pair` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为互加好友',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friend`
--

LOCK TABLES `friend` WRITE;
/*!40000 ALTER TABLE `friend` DISABLE KEYS */;
/*!40000 ALTER TABLE `friend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小组id',
  `title` varchar(100) NOT NULL COMMENT '小组名称',
  `about` text COMMENT '小组介绍',
  `logo` varchar(100) NOT NULL DEFAULT '' COMMENT 'logo',
  `backgroundLogo` varchar(100) NOT NULL DEFAULT '',
  `status` enum('open','close') NOT NULL DEFAULT 'open',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `ownerId` int(10) unsigned NOT NULL COMMENT '小组组长id',
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建小组时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'بىلىگ','<p>بىلىگ دەرسخانىسىغا كەلگىنىڭىزنى قىزغىن قارشى ئالىمىز! سەرخىل دەرسلىكلەر بېكىتىمىزدە!</p>\n','public://default/2016/01-28/122815f1f6dd744960.jpg','public://default/2016/01-20/122903f48c29772900.jpg','open',1,0,0,1,1453264017),(2,'لايىھەلەش','<p>فوتوشوپ ئۆگۈنىش سىنىپى! سەرخىل دەرسلىكلەر بېكىتىمىزدە!</p>\n','public://default/2016/01-28/122748467dd9830671.jpg','','open',1,0,0,1,1453264211),(3,'توربېكەت قۇرۇش','<p>تېز سۈرئەتتە توربېكەت قۇرۇشنى ئۆگۈنۈش دەرسلىكى باشلاندى!  سەرخىل دەرسلىكلەر بېكىتىمىزدە!</p>\n','public://default/2016/01-28/12283869f137651110.jpg','','open',1,1,0,1,1453264279);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_member`
--

DROP TABLE IF EXISTS `groups_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '成员id主键',
  `groupId` int(10) unsigned NOT NULL COMMENT '小组id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `role` varchar(100) NOT NULL DEFAULT 'member',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(11) unsigned NOT NULL COMMENT '加入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_member`
--

LOCK TABLES `groups_member` WRITE;
/*!40000 ALTER TABLE `groups_member` DISABLE KEYS */;
INSERT INTO `groups_member` VALUES (1,1,1,'owner',0,0,1453264017),(2,2,1,'owner',0,0,1453264211),(3,3,1,'owner',0,1,1453264279);
/*!40000 ALTER TABLE `groups_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_thread`
--

DROP TABLE IF EXISTS `groups_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '话题id',
  `title` varchar(1024) NOT NULL COMMENT '话题标题',
  `content` text COMMENT '话题内容',
  `isElite` int(11) unsigned NOT NULL DEFAULT '0',
  `isStick` int(11) unsigned NOT NULL DEFAULT '0',
  `lastPostMemberId` int(10) unsigned NOT NULL DEFAULT '0',
  `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0',
  `groupId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL COMMENT '添加时间',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('open','close') NOT NULL DEFAULT 'open',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
  `rewardCoin` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT 'default',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_thread`
--

LOCK TABLES `groups_thread` WRITE;
/*!40000 ALTER TABLE `groups_thread` DISABLE KEYS */;
INSERT INTO `groups_thread` VALUES (1,'توربېكەت قۇرۇش','<p>سەرخىل دەرسلىكلەر بېكىتىمىزدە</p>\n',0,0,0,0,3,1,1453264702,0,'open',1,0,'default',1453264702);
/*!40000 ALTER TABLE `groups_thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_thread_collect`
--

DROP TABLE IF EXISTS `groups_thread_collect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_collect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `threadId` int(11) unsigned NOT NULL COMMENT '收藏的话题id',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人id',
  `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_thread_collect`
--

LOCK TABLES `groups_thread_collect` WRITE;
/*!40000 ALTER TABLE `groups_thread_collect` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups_thread_collect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_thread_goods`
--

DROP TABLE IF EXISTS `groups_thread_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('content','attachment','postAttachment') NOT NULL,
  `threadId` int(10) unsigned NOT NULL,
  `postId` int(10) unsigned NOT NULL DEFAULT '0',
  `coin` int(10) unsigned NOT NULL,
  `fileId` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_thread_goods`
--

LOCK TABLES `groups_thread_goods` WRITE;
/*!40000 ALTER TABLE `groups_thread_goods` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups_thread_goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_thread_post`
--

DROP TABLE IF EXISTS `groups_thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `threadId` int(11) unsigned NOT NULL COMMENT '话题id',
  `content` text NOT NULL COMMENT '回复内容',
  `userId` int(10) unsigned NOT NULL COMMENT '回复人id',
  `fromUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `postId` int(10) unsigned DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL COMMENT '回复时间',
  `adopt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_thread_post`
--

LOCK TABLES `groups_thread_post` WRITE;
/*!40000 ALTER TABLE `groups_thread_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups_thread_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_thread_trade`
--

DROP TABLE IF EXISTS `groups_thread_trade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_trade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned DEFAULT '0',
  `goodsId` int(10) DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_thread_trade`
--

LOCK TABLES `groups_thread_trade` WRITE;
/*!40000 ALTER TABLE `groups_thread_trade` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups_thread_trade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installed_packages`
--

DROP TABLE IF EXISTS `installed_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `installed_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ename` varchar(255) NOT NULL COMMENT '包名称',
  `cname` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL COMMENT 'version',
  `installTime` int(11) NOT NULL COMMENT '安装时间',
  `fromVersion` varchar(255) NOT NULL DEFAULT '' COMMENT '来源',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cname` (`ename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已安装包';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installed_packages`
--

LOCK TABLES `installed_packages` WRITE;
/*!40000 ALTER TABLE `installed_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `installed_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invite_record`
--

DROP TABLE IF EXISTS `invite_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inviteUserId` int(11) unsigned DEFAULT NULL COMMENT '邀请者',
  `invitedUserId` int(11) unsigned DEFAULT NULL COMMENT '被邀请者',
  `inviteTime` int(11) unsigned DEFAULT NULL COMMENT '邀请时间',
  `inviteUserCardId` int(11) unsigned DEFAULT NULL COMMENT '邀请者获得奖励的卡的ID',
  `invitedUserCardId` int(11) unsigned DEFAULT NULL COMMENT '被邀请者获得奖励的卡的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邀请记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invite_record`
--

LOCK TABLES `invite_record` WRITE;
/*!40000 ALTER TABLE `invite_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `invite_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist`
--

DROP TABLE IF EXISTS `ip_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('failed','banned') COLLATE utf8_unicode_ci NOT NULL COMMENT 'failed,banned(DC2Type:enum)',
  `counter` int(10) unsigned NOT NULL DEFAULT '0',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist`
--

LOCK TABLES `ip_blacklist` WRITE;
/*!40000 ALTER TABLE `ip_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keyword`
--

DROP TABLE IF EXISTS `keyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keyword` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `state` enum('','replaced','banned') NOT NULL,
  `bannedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keyword`
--

LOCK TABLES `keyword` WRITE;
/*!40000 ALTER TABLE `keyword` DISABLE KEYS */;
/*!40000 ALTER TABLE `keyword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keyword_banlog`
--

DROP TABLE IF EXISTS `keyword_banlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keyword_banlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keywordId` int(10) unsigned NOT NULL,
  `keywordName` varchar(64) NOT NULL DEFAULT '',
  `state` enum('banned','replaced') NOT NULL,
  `text` text NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keywordId` (`keywordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keyword_banlog`
--

LOCK TABLES `keyword_banlog` WRITE;
/*!40000 ALTER TABLE `keyword_banlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `keyword_banlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` bigint(20) unsigned NOT NULL,
  `parentId` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `pinyin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `data` text,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `level` char(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=335 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,0,'user','change_role','设置用户测试管理员(#1)的角色为：ROLE_USER,ROLE_SUPER_ADMIN,ROLE_TEACHER','','127.0.0.1',1452852364,'info'),(2,0,'category','create','添加分类 默认分类(#1)','{\"id\":\"1\",\"code\":\"default\",\"name\":\"\\u9ed8\\u8ba4\\u5206\\u7c7b\",\"icon\":\"\",\"path\":\"\",\"weight\":\"100\",\"groupId\":\"1\",\"parentId\":\"0\",\"description\":null}','127.0.0.1',1452852365,'info'),(3,0,'tag','create','添加标签默认标签(#1)','','127.0.0.1',1452852365,'info'),(4,0,'user','change_role','设置用户测试管理员(#1)的角色为：ROLE_USER,ROLE_SUPER_ADMIN,ROLE_TEACHER','','127.0.0.1',1452852819,'info'),(5,0,'category','delete','删除分类默认分类(#1)','','127.0.0.1',1452852820,'info'),(6,0,'category','create','添加分类 默认分类(#2)','{\"id\":\"2\",\"code\":\"default\",\"name\":\"\\u9ed8\\u8ba4\\u5206\\u7c7b\",\"icon\":\"\",\"path\":\"\",\"weight\":\"100\",\"groupId\":\"2\",\"parentId\":\"0\",\"description\":null}','127.0.0.1',1452852820,'info'),(7,0,'block','update','更新编辑区#2','{\"content\":\"  <a href=\\\"#\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg?6.12.2\\\" \\/><\\/a>\\n  <a href=\\\"#\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg?6.12.2\\\" \\/><\\/a>\\n  <a href=\\\"#\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg?6.12.2\\\" \\/><\\/a>\\n  <a href=\\\"#\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/live-slide-2.jpg?6.12.2\\\" \\/><\\/a>\\n  <a href=\\\"#\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/live-slide-1.jpg?6.12.2\\\" \\/><\\/a>\\n\"}','127.0.0.1',1452852822,'info'),(8,0,'block','update','更新编辑区#3','{\"content\":\"  <a href=\\\"#\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-1.png\\\" alt=\\\"\\u8f6e\\u64ad\\u56fe1\\u63cf\\u8ff0\\\"><\\/a>\\n  <a href=\\\"#\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-2.png\\\" alt=\\\"\\u8f6e\\u64ad\\u56fe2\\u63cf\\u8ff0\\\"><\\/a>\\n  <a href=\\\"#\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/img\\/placeholder\\/carousel-1200x256-3.png\\\" alt=\\\"\\u8f6e\\u64ad\\u56fe3\\u63cf\\u8ff0\\\"><\\/a>\\n\"}','127.0.0.1',1452852822,'info'),(9,0,'block','update','更新编辑区#4','{\"content\":\"  <div class=\\\"item active\\\">\\n    <a href=\\\"\\/#?6.12.2\\\" target=\\\"_blank\\\"><img src=\\\"\\/themes\\/autumn\\/img\\/slide-1.jpg?6.12.2\\\" alt=\\\"\\u56fe\\u7247\\uff11\\u7684\\u63cf\\u8ff0\\\"><\\/a>\\n  <\\/div>\\n  <div class=\\\"item \\\">\\n    <a href=\\\"\\/#?6.12.2\\\" target=\\\"_self\\\"><img src=\\\"\\/themes\\/autumn\\/img\\/slide-2.jpg?6.12.2\\\" alt=\\\"\\u56fe\\u7247\\uff12\\u7684\\u63cf\\u8ff0\\\"><\\/a>\\n  <\\/div>\\n  <div class=\\\"item \\\">\\n    <a href=\\\"\\/#?6.12.2\\\" target=\\\"_blank\\\"><img src=\\\"\\/themes\\/autumn\\/img\\/slide-3.jpg?6.12.2\\\" alt=\\\"\\u56fe\\u7247\\uff13\\u7684\\u63cf\\u8ff0\\\"><\\/a>\\n  <\\/div>\\n\"}','127.0.0.1',1452852822,'info'),(10,0,'block','update','更新编辑区#5','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3ec768;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/jianmo\\/img\\/banner_net.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/jianmo\\/img\\/banner_app.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/jianmo\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1452852822,'info'),(11,0,'block','update','更新编辑区#6','{\"content\":\"<section class=\\\"introduction-section\\\">\\n  <div class=\\\"container hidden-xs\\\">\\n    <div class=\\\"row\\\">\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\\\">\\n          <h4>\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927<\\/h4>\\n          <h5>\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\\\">\\n          <h4>\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f<\\/h4>\\n          <h5>\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\\\">\\n          <h4>\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301<\\/h4>\\n          <h5>\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7<\\/h5>\\n        <\\/div>\\n          <\\/div>\\n  <\\/div>\\n<\\/section>\"}','127.0.0.1',1452852822,'info'),(12,0,'block','update','更新编辑区#7','{\"content\":\"\\n<div class=\\\"col-md-8 footer-main clearfix\\\">\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u5b66\\u751f<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u6ce8\\u518c<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u5b66\\u4e60<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u4e92\\u52a8<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u8001\\u5e08<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\\\" target=\\\"_blank\\\">\\u53d1\\u5e03\\u8bfe\\u7a0b<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\\\" target=\\\"_blank\\\">\\u4f7f\\u7528\\u9898\\u5e93<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\\\" target=\\\"_blank\\\">\\u6559\\u5b66\\u8d44\\u6599\\u5e93<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u7ba1\\u7406\\u5458<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\\\" target=\\\"_blank\\\">\\u7cfb\\u7edf\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\\\" target=\\\"_blank\\\">\\u8bfe\\u7a0b\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u7ba1\\u7406<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5546\\u4e1a\\u5e94\\u7528<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\\\" target=\\\"_blank\\\">\\u4f1a\\u5458\\u4e13\\u533a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\\\" target=\\\"_blank\\\">\\u9898\\u5e93\\u589e\\u5f3a\\u7248<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5173\\u4e8e\\u6211\\u4eec<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/\\\" target=\\\"_blank\\\">ES\\u5b98\\u7f51<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\\\" target=\\\"_blank\\\">\\u5b98\\u65b9\\u5fae\\u535a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\\\" target=\\\"_blank\\\">\\u52a0\\u5165\\u6211\\u4eec<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n<\\/div>\\n\\n<div class=\\\"col-md-4 footer-logo hidden-sm hidden-xs\\\">\\n  <a class=\\\"\\\" href=\\\"http:\\/\\/www.edusoho.com\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/v2\\/img\\/bottom_logo.png?6.12.2\\\" alt=\\\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\\\"><\\/a>\\n  <div class=\\\"footer-sns\\\">\\n        <a href=\\\"http:\\/\\/weibo.com\\/edusoho\\\" target=\\\"_blank\\\"><i class=\\\"es-icon es-icon-weibo\\\"><\\/i><\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-weixin\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/weixin.png?6.12.2\\\" alt=\\\"\\\">  \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-apple\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/apple.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-android\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/android.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n      <\\/div>\\n<\\/div>\\n\\n\\n\"}','127.0.0.1',1452852823,'info'),(13,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1440528062\",\"latestExecutedTime\":\"1440524462\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1452852915,'info'),(14,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1440528062\",\"latestExecutedTime\":\"1440524462\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1452852915,'info'),(15,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1440528069\",\"latestExecutedTime\":\"1440524469\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1452852918,'info'),(16,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1440528069\",\"latestExecutedTime\":\"1440524469\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1452852918,'info'),(17,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1452856515\",\"latestExecutedTime\":\"1452852915\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453003449,'info'),(18,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1452856515\",\"latestExecutedTime\":\"1452852915\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453003450,'info'),(19,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1452856518\",\"latestExecutedTime\":\"1452852918\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453003553,'info'),(20,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1452856518\",\"latestExecutedTime\":\"1452852918\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453003553,'info'),(21,1,'user','login_success','登录成功','','127.0.0.1',1453005971,'info'),(22,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453007050\",\"latestExecutedTime\":\"1453003450\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453007430,'info'),(23,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453007050\",\"latestExecutedTime\":\"1453003450\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453007430,'info'),(24,1,'user','login_success','登录成功','','127.0.0.1',1453023137,'info'),(25,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453007153\",\"latestExecutedTime\":\"1453003553\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453023139,'info'),(26,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453007153\",\"latestExecutedTime\":\"1453003553\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453023140,'info'),(27,1,'user','login_success','登录成功','','127.0.0.1',1453033192,'info'),(28,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453011030\",\"latestExecutedTime\":\"1453007430\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453033194,'info'),(29,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453011030\",\"latestExecutedTime\":\"1453007430\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453033195,'info'),(30,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453026740\",\"latestExecutedTime\":\"1453023140\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453033203,'info'),(31,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453026740\",\"latestExecutedTime\":\"1453023140\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453033203,'info'),(32,1,'user','login_success','登录成功','','127.0.0.1',1453040614,'info'),(33,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453036794\",\"latestExecutedTime\":\"1453033194\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453040615,'info'),(34,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453036794\",\"latestExecutedTime\":\"1453033194\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453040615,'info'),(35,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453036803\",\"latestExecutedTime\":\"1453033203\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453040616,'info'),(36,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453036803\",\"latestExecutedTime\":\"1453033203\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453040616,'info'),(37,1,'user','login_success','登录成功','','127.0.0.1',1453041109,'info'),(38,1,'info','navigation_create','创建导航باشبەت','','127.0.0.1',1453041223,'info'),(39,1,'system','update_settings','更新QQ客服设置','{\"enabled\":\"1\",\"color\":\"default\",\"qq\":[{\"name\":\"\",\"number\":\"\",\"url\":\"\"}],\"qqgroup\":[{\"name\":\"\",\"number\":\"\",\"url\":\"\"}],\"worktime\":\"9:00 - 17:00\",\"phone\":[{\"name\":\"\",\"number\":\"\"}],\"file\":\"\",\"webchatURI\":\"\",\"email\":\"\"}','127.0.0.1',1453041232,'info'),(40,1,'system','update_settings','更新注册设置','{\"register_mode\":\"email\",\"email_enabled\":\"closed\",\"setting_time\":1453041259,\"email_activation_title\":\"\\u8bf7\\u6fc0\\u6d3b\\u60a8\\u7684{{sitename}}\\u5e10\\u53f7\",\"email_activation_body\":\"Hi, {{nickname}}\\r\\n\\r\\n\\u6b22\\u8fce\\u52a0\\u5165{{sitename}}!\\r\\n\\r\\n\\u8bf7\\u70b9\\u51fb\\u4e0b\\u9762\\u7684\\u94fe\\u63a5\\u5b8c\\u6210\\u6ce8\\u518c\\uff1a\\r\\n\\r\\n{{verifyurl}}\\r\\n\\r\\n\\u5982\\u679c\\u4ee5\\u4e0a\\u94fe\\u63a5\\u65e0\\u6cd5\\u70b9\\u51fb\\uff0c\\u8bf7\\u5c06\\u4e0a\\u9762\\u7684\\u5730\\u5740\\u590d\\u5236\\u5230\\u4f60\\u7684\\u6d4f\\u89c8\\u5668(\\u5982IE)\\u7684\\u5730\\u5740\\u680f\\u4e2d\\u6253\\u5f00\\uff0c\\u8be5\\u94fe\\u63a5\\u5730\\u574024\\u5c0f\\u65f6\\u5185\\u6253\\u5f00\\u6709\\u6548\\u3002\\r\\n\\r\\n\\u611f\\u8c22\\u5bf9{{sitename}}\\u7684\\u652f\\u6301\\uff01\\r\\n\\r\\n{{sitename}} {{siteurl}}\\r\\n\\r\\n(\\u8fd9\\u662f\\u4e00\\u5c01\\u81ea\\u52a8\\u4ea7\\u751f\\u7684email\\uff0c\\u8bf7\\u52ff\\u56de\\u590d\\u3002)\",\"welcome_enabled\":\"opened\",\"welcome_sender\":\"\\u6d4b\\u8bd5\\u7ba1\\u7406\\u5458\",\"welcome_methods\":[],\"welcome_title\":\"\\u6b22\\u8fce\\u52a0\\u5165{{sitename}}\",\"welcome_body\":\"\\u60a8\\u597d{{nickname}}\\uff0c\\u6211\\u662f{{sitename}}\\u7684\\u7ba1\\u7406\\u5458\\uff0c\\u6b22\\u8fce\\u52a0\\u5165{{sitename}}\\uff0c\\u795d\\u60a8\\u5b66\\u4e60\\u6109\\u5feb\\u3002\\u5982\\u6709\\u95ee\\u9898\\uff0c\\u968f\\u65f6\\u4e0e\\u6211\\u8054\\u7cfb\\u3002\",\"user_terms\":\"opened\",\"user_terms_body\":\"\",\"captcha_enabled\":0,\"register_protective\":\"none\",\"nickname_enabled\":0,\"avatar_alert\":\"none\",\"_cloud_sms\":\"\"}','127.0.0.1',1453041259,'info'),(41,1,'system','update_settings','更新开发者设置','{\"debug\":\"1\",\"app_api_url\":\"\",\"cloud_api_server\":\"http:\\/\\/api.edusoho.net\",\"cloud_api_tui_server\":\"\",\"hls_encrypted\":\"1\",\"balloon_player\":\"1\",\"without_network\":\"1\"}','127.0.0.1',1453041305,'info'),(42,1,'system','update_settings','更新开发者设置','{\"debug\":\"1\",\"app_api_url\":\"\",\"cloud_api_server\":\"http:\\/\\/api.edusoho.net\",\"cloud_api_tui_server\":\"\",\"hls_encrypted\":\"1\",\"balloon_player\":\"1\",\"without_network\":\"0\"}','127.0.0.1',1453041327,'info'),(43,1,'system','update_settings','更新登录设置','{\"login_limit\":\"1\",\"enabled\":\"1\",\"temporary_lock_enabled\":\"1\",\"temporary_lock_allowed_times\":\"5\",\"ip_temporary_lock_allowed_times\":\"20\",\"temporary_lock_minutes\":\"20\",\"weibo_enabled\":\"1\",\"weibo_key\":\"ddsdsadasdasdasdasaddasdas\",\"weibo_secret\":\"ddsdsadasdasdasdasaddasdas\",\"weibo_set_fill_account\":\"1\",\"qq_enabled\":\"1\",\"qq_key\":\"ddsdsadasdasdasdasaddasdas\",\"qq_secret\":\"ddsdsadasdasdasdasaddasdas\",\"qq_set_fill_account\":\"1\",\"renren_enabled\":\"1\",\"renren_key\":\"ddsdsadasdasdasdasaddasdas\",\"renren_secret\":\"ddsdsadasdasdasdasaddasdas\",\"renren_set_fill_account\":\"1\",\"weixinweb_enabled\":\"1\",\"weixinweb_key\":\"ddsdsadasdasdasdasaddasdas\",\"weixinweb_secret\":\"ddsdsadasdasdasdasaddasdas\",\"weixinweb_set_fill_account\":\"1\",\"weixinmob_enabled\":\"1\",\"weixinmob_key\":\"ddsdsadasdasdasdasaddasdas\",\"weixinmob_secret\":\"ddsdsadasdasdasdasaddasdas\",\"weixinmob_set_fill_account\":\"1\",\"verify_code\":\"\"}','127.0.0.1',1453041414,'info'),(44,1,'system','update_settings','更新课程设置','{\"welcome_message_enabled\":\"1\",\"welcome_message_body\":\"{{nickname}},\\u6b22\\u8fce\\u52a0\\u5165\\u8bfe\\u7a0b{{course}}\",\"teacher_modify_price\":\"1\",\"teacher_search_order\":\"1\",\"teacher_manage_student\":\"1\",\"teacher_export_student\":\"0\",\"student_download_media\":\"0\",\"explore_default_orderBy\":\"latest\",\"relatedCourses\":\"1\",\"allowAnonymousPreview\":\"1\",\"copy_enabled\":\"1\",\"testpaperCopy_enabled\":\"1\",\"show_student_num_enabled\":\"1\",\"custom_chapter_enabled\":\"1\",\"chapter_name\":\"\\u7ae0\",\"part_name\":\"\\u8282\",\"userinfoFields\":[],\"userinfoFieldNameArray\":[]}','127.0.0.1',1453041461,'info'),(45,1,'system','update_settings','更新课程设置','{\"welcome_message_enabled\":\"1\",\"welcome_message_body\":\"{{nickname}},\\u6b22\\u8fce\\u52a0\\u5165\\u8bfe\\u7a0b{{course}}\",\"teacher_modify_price\":\"1\",\"teacher_search_order\":\"1\",\"teacher_manage_student\":\"1\",\"teacher_export_student\":\"0\",\"student_download_media\":\"0\",\"explore_default_orderBy\":\"latest\",\"relatedCourses\":\"1\",\"allowAnonymousPreview\":\"1\",\"copy_enabled\":\"1\",\"testpaperCopy_enabled\":\"1\",\"show_student_num_enabled\":\"1\",\"custom_chapter_enabled\":\"1\",\"chapter_name\":\"\\u7ae0\",\"part_name\":\"\\u8282\",\"userinfoFields\":[],\"userinfoFieldNameArray\":[],\"live_course_enabled\":\"1\",\"live_student_capacity\":0}','127.0.0.1',1453041474,'info'),(46,1,'system','update_settings','更支付方式设置','{\"enabled\":\"1\",\"disabled_message\":\"\\u5c1a\\u672a\\u5f00\\u542f\\u652f\\u4ed8\\u6a21\\u5757\\uff0c\\u65e0\\u6cd5\\u8d2d\\u4e70\\u8bfe\\u7a0b\\u3002\",\"alipay_enabled\":\"1\",\"alipay_type\":\"direct\",\"alipay_key\":\"ddsdsadasdasdasdasaddasdas\",\"alipay_secret\":\"ddsdsadasdasdasdasaddasdas\",\"alipay_account\":\"torghay@bilig.biz\",\"close_trade_enabled\":\"1\",\"wxpay_enabled\":\"1\",\"wxpay_key\":\"ddsdsadasdasdasdasaddasdas\",\"wxpay_account\":\"dsdasdasdasda\",\"wxpay_secret\":\"ddsdsadasdasdasdasaddasdas\",\"heepay_enabled\":\"1\",\"heepay_key\":\"ddsdsadasdasdasdasaddasdas\",\"heepay_secret\":\"ddsdsadasdasdasdasaddasdas\",\"quickpay_enabled\":\"1\",\"quickpay_key\":\"ddsdsadasdasdasdasaddasdas\",\"quickpay_secret\":\"ddsdsadasdasdasdasaddasdas\",\"quickpay_aes\":\"ddsdsadasdasdasdasaddasdas\"}','127.0.0.1',1453041539,'info'),(47,1,'system','update_settings','更新移动客户端设置','{\"enabled\":\"1\",\"ver\":\"1\",\"about\":\"\",\"logo\":\"\",\"notice\":\"\",\"splash1\":\"\",\"splash2\":\"\",\"splash3\":\"\",\"splash4\":\"\",\"splash5\":\"\"}','127.0.0.1',1453041592,'info'),(48,1,'system','update_settings','更新移动客户端设置','{\"enabled\":\"1\",\"ver\":\"1\",\"about\":\"\",\"logo\":\"\",\"notice\":\"\",\"splash1\":\"\",\"splash2\":\"\",\"splash3\":\"\",\"splash4\":\"\",\"splash5\":\"\"}','127.0.0.1',1453041593,'info'),(49,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453044215\",\"latestExecutedTime\":\"1453040615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453044827,'info'),(50,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453044215\",\"latestExecutedTime\":\"1453040615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453044827,'info'),(51,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453044216\",\"latestExecutedTime\":\"1453040616\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453105995,'info'),(52,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453044216\",\"latestExecutedTime\":\"1453040616\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453105995,'info'),(53,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453048427\",\"latestExecutedTime\":\"1453044827\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453106012,'info'),(54,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453048427\",\"latestExecutedTime\":\"1453044827\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453106012,'info'),(55,1,'user','login_success','登录成功','','192.168.31.133',1453106039,'info'),(56,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453109595\",\"latestExecutedTime\":\"1453105995\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453132819,'info'),(57,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453109595\",\"latestExecutedTime\":\"1453105995\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453132819,'info'),(58,1,'user','login_success','登录成功','','192.168.31.133',1453193752,'info'),(59,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453109612\",\"latestExecutedTime\":\"1453106012\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453193755,'info'),(60,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453109612\",\"latestExecutedTime\":\"1453106012\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453193755,'info'),(61,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453136419\",\"latestExecutedTime\":\"1453132819\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453209922,'info'),(62,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453136419\",\"latestExecutedTime\":\"1453132819\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453209922,'info'),(63,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453197355\",\"latestExecutedTime\":\"1453193755\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453212967,'info'),(64,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453197355\",\"latestExecutedTime\":\"1453193755\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453212967,'info'),(65,1,'user','login_success','登录成功','','127.0.0.1',1453212976,'info'),(66,1,'user','login_success','登录成功','','127.0.0.1',1453213695,'info'),(67,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453213522\",\"latestExecutedTime\":\"1453209922\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453213699,'info'),(68,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453213522\",\"latestExecutedTime\":\"1453209922\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453213700,'info'),(69,1,'course','create','创建课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)','','127.0.0.1',1453213757,'info'),(70,1,'course','update','更新课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)的信息','{\"title\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643 \\u0626\\u0649\\u0634\\u0644\\u06d5\\u0634 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\",\"subtitle\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649 \\u0626\\u06d0\\u0643\\u0631\\u0627\\u0646\\u0646\\u0649 \\u0633\\u06c8\\u0631\\u06d5\\u062a\\u0643\\u06d5 \\u0626\\u06d0\\u0644\\u0649\\u0634 \\u062f\\u06d5\\u0633\\u0644\\u0649\\u0643\\u0649\\u060c \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643 \\u0626\\u06c8\\u0644\\u06af\\u0649\\u0633\\u0649\",\"expiryDay\":30,\"serializeMode\":\"serialize\",\"categoryId\":2,\"tags\":[1],\"buyable\":1}','127.0.0.1',1453213783,'info'),(71,1,'course','update','更新课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)的信息','{\"about\":\"<p style=\\\"text-align:right;\\\">\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649 \\u0642\\u06c7\\u0631\\u0649\\u0644\\u063a\\u0627\\u0646\\u062f\\u0649\\u0646 \\u0628\\u06c7\\u064a\\u0627\\u0646\\u0642\\u0649 \\u0626\\u06c8\\u0686 \\u064a\\u0649\\u0644 \\u062c\\u06d5\\u0631\\u064a\\u0627\\u0646\\u0649\\u062f\\u0627 \\u0626\\u06c8\\u0632\\u0644\\u06c8\\u0643\\u0633\\u0649\\u0632 \\u064a\\u06d0\\u06ad\\u0649\\u0644\\u0627\\u0634 \\u0646\\u06d5\\u062a\\u0649\\u062c\\u0649\\u0633\\u0649\\u062f\\u06d5 \\u0626\\u06c7\\u064a\\u063a\\u06c7\\u0631\\u0644\\u0627\\u0631\\u062f\\u0649\\u0643\\u0649 \\u0626\\u06d5\\u06ad \\u0645\\u06c7\\u0643\\u06d5\\u0645\\u0645\\u06d5\\u0644 \\u0633\\u06d0\\u0633\\u062a\\u0649\\u0645\\u0649\\u063a\\u0627 \\u0626\\u0649\\u06af\\u06d5 \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\\u0646\\u0649 \\u0634\\u06d5\\u0643\\u0649\\u0644\\u0644\\u06d5\\u0646\\u062f\\u06c8\\u0631\\u062f\\u0649. \\u064a\\u06d0\\u0642\\u0649\\u0646\\u062f\\u0627 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0645\\u0649\\u0632 \\u062a\\u0648\\u0631\\u062f\\u0627\\u0634\\u0644\\u0627\\u0631\\u0646\\u0649\\u06ad \\u0643\\u06c8\\u0686\\u0644\\u06c8\\u0643 \\u062a\\u06d5\\u0644\\u0649\\u067e\\u0649\\u0646\\u0649 \\u0642\\u0627\\u0646\\u062f\\u06c7\\u0631\\u06c7\\u0634 \\u0645\\u06d5\\u0642\\u0633\\u0649\\u062a\\u0649\\u062f\\u06d5\\u060c \\u0628\\u0649\\u0631\\u0642\\u0649\\u0633\\u0649\\u0645 \\u062a\\u0649\\u0644 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\\u060c \\u0643\\u06d5\\u0633\\u067e\\u0649\\u064a \\u0626\\u06c6\\u06af\\u06c8\\u0646\\u06c8\\u0634 \\u062f\\u06d5\\u0633\\u0644\\u0649\\u0643\\u0649 \\u0642\\u0627\\u062a\\u0627\\u0631\\u0644\\u0649\\u0642 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u06d5\\u0631\\u0646\\u0649 \\u0633\\u0649\\u0646\\u063a\\u0627 \\u0626\\u06d0\\u0644\\u0649\\u067e \\u0642\\u0648\\u064a\\u06c7\\u0634 \\u0626\\u06c8\\u0686\\u06c8\\u0646 \\u0628\\u06c7 \\u062c\\u06d5\\u06be\\u06d5\\u062a\\u062a\\u06d5 \\u0626\\u0627\\u0644\\u0627\\u06be\\u0649\\u062f\\u0649\\u0644\\u0649\\u0643\\u0649 \\u0628\\u0648\\u0644\\u063a\\u0627\\u0646 \\u0626\\u0649\\u0642\\u062a\\u0649\\u0633\\u0627\\u0633\\u0644\\u0649\\u0642\\u0644\\u0627\\u0631\\u062f\\u0649\\u0646 \\u0626\\u0648\\u0642\\u06c7\\u062a\\u0642\\u06c7\\u0686\\u0649 \\u0642\\u0648\\u0628\\u06c7\\u0644 \\u0642\\u0649\\u0644\\u0645\\u0627\\u0642\\u0686\\u0649.<\\/p>\\n\\n<p style=\\\"text-align:right;\\\">\\u06be\\u06d0\\u0686\\u0642\\u0627\\u0646\\u062f\\u0627\\u0642 \\u06cb\\u0627\\u0642\\u0649\\u062a\\u060c \\u0631\\u0627\\u064a\\u0648\\u0646 \\u0686\\u06d5\\u0643\\u0644\\u0649\\u0645\\u0649\\u0633\\u0649\\u06af\\u06d5 \\u0626\\u06c7\\u0686\\u0649\\u0631\\u0649\\u0645\\u0627\\u064a\\u0633\\u0649\\u0632\\u060c \\u0645\\u06d5\\u064a\\u0644\\u0649 \\u0633\\u0649\\u0632\\u0646\\u0649\\u06ad \\u0642\\u0627\\u0646\\u062f\\u0627\\u0642\\u0644\\u0627 \\u06cb\\u0627\\u0642\\u0649\\u062a\\u062a\\u0627 \\u0628\\u0648\\u0634 \\u06cb\\u0627\\u0642\\u062a\\u0649\\u06ad\\u0649\\u0632 \\u0628\\u0648\\u0644\\u0633\\u06c7\\u0646 \\u0628\\u0649\\u0645\\u0627\\u0644\\u0627\\u0644 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643 \\u0626\\u0649\\u0634\\u0644\\u0649\\u064a\\u06d5\\u0644\\u06d5\\u064a\\u0633\\u0632. \\u0645\\u06c7\\u0643\\u06d5\\u0645\\u0645\\u06d5\\u0644 \\u0626\\u0649\\u0642\\u062a\\u0649\\u062f\\u0627\\u0631\\u0644\\u0649\\u0642 \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649 \\u067e\\u06d5\\u0642\\u06d5\\u062a \\u0633\\u0649\\u0632\\u0646\\u0649\\u06ad \\u0633\\u0649\\u0646\\u0649\\u067e \\u0645\\u06c7\\u0646\\u0628\\u0649\\u0631\\u0649\\u062f\\u0649\\u0643\\u0649 \\u0626\\u0648\\u0631\\u0646\\u0649\\u06ad\\u0649\\u0632\\u0646\\u0649 \\u062a\\u0648\\u0631\\u063a\\u0627 \\u064a\\u06c6\\u062a\\u0643\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u0646\\u0649\\u0644\\u0627 \\u0643\\u06c8\\u062a\\u06c8\\u067e \\u062a\\u06c7\\u0631\\u0645\\u0627\\u0642\\u062a\\u0627. \\u0628\\u0649\\u0644\\u0649\\u0645\\u0649\\u06ad\\u0649\\u0632 \\u0626\\u0627\\u0631\\u0642\\u0649\\u0644\\u0649\\u0642 \\u06be\\u06d0\\u0686\\u0642\\u0627\\u0646\\u062f\\u0627\\u0642 \\u0686\\u06d5\\u0643\\u0644\\u0649\\u0645\\u0649\\u0633\\u0649\\u0632\\u060c \\u06be\\u06d0\\u0686\\u0642\\u0627\\u0646\\u062f\\u0627\\u0642 \\u0686\\u0649\\u0642\\u0649\\u0645\\u0633\\u0649\\u0632 \\u0626\\u0649\\u0642\\u062a\\u0649\\u0633\\u0627\\u062f\\u0649\\u064a \\u0642\\u0649\\u0645\\u0645\\u06d5\\u062a \\u064a\\u0627\\u0631\\u062a\\u0649\\u0634\\u0646\\u0649 \\u062e\\u0627\\u0644\\u0627\\u0645\\u0633\\u0649\\u0632\\u061f<\\/p>\\n\",\"goals\":[],\"audiences\":[]}','127.0.0.1',1453213871,'info'),(72,1,'course','publish','发布课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)','','127.0.0.1',1453213913,'info'),(73,1,'course','publish','发布课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)','','127.0.0.1',1453213915,'info'),(74,1,'user','login_success','登录成功','','127.0.0.1',1453218002,'info'),(75,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453216567\",\"latestExecutedTime\":\"1453212967\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453218002,'info'),(76,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453216567\",\"latestExecutedTime\":\"1453212967\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453218002,'info'),(77,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453217300\",\"latestExecutedTime\":\"1453213700\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453218005,'info'),(78,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453217300\",\"latestExecutedTime\":\"1453213700\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453218005,'info'),(79,1,'course','create','创建课程《Java ئاساسىي بىلىم دەرسلىكى》(#2)','','127.0.0.1',1453221397,'info'),(80,1,'course','update','更新课程《Java ئاساسىي بىلىم دەرسلىكى》(#2)的信息','{\"title\":\"Java \\u0626\\u0627\\u0633\\u0627\\u0633\\u0649\\u064a \\u0628\\u0649\\u0644\\u0649\\u0645 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\",\"subtitle\":\"Java \\u0626\\u0627\\u0633\\u0627\\u0633\\u0649\\u064a \\u0628\\u0649\\u0644\\u0649\\u0645 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649, Java \\u0626\\u0627\\u0633\\u0627\\u0633\\u0649\\u064a \\u0628\\u0649\\u0644\\u0649\\u0645 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649.\",\"expiryDay\":10,\"serializeMode\":\"finished\",\"categoryId\":2,\"tags\":[1],\"buyable\":1}','127.0.0.1',1453221420,'info'),(81,1,'course','publish','发布课程《Java ئاساسىي بىلىم دەرسلىكى》(#2)','','127.0.0.1',1453221472,'info'),(82,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3ec768;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453221539,'info'),(83,1,'block','update','更新编辑区#12','{\"content\":\"<section class=\\\"introduction-section\\\">\\n  <div class=\\\"container hidden-xs\\\">\\n    <div class=\\\"row\\\">\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\\\">\\n          <h4>\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927<\\/h4>\\n          <h5>\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\\\">\\n          <h4>\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f<\\/h4>\\n          <h5>\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\\\">\\n          <h4>\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301<\\/h4>\\n          <h5>\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7<\\/h5>\\n        <\\/div>\\n          <\\/div>\\n  <\\/div>\\n<\\/section>\"}','127.0.0.1',1453221539,'info'),(84,1,'block','update','更新编辑区#13','{\"content\":\"\\n<div class=\\\"col-md-8 footer-main clearfix\\\">\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u5b66\\u751f<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u6ce8\\u518c<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u5b66\\u4e60<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u4e92\\u52a8<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u8001\\u5e08<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\\\" target=\\\"_blank\\\">\\u53d1\\u5e03\\u8bfe\\u7a0b<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\\\" target=\\\"_blank\\\">\\u4f7f\\u7528\\u9898\\u5e93<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\\\" target=\\\"_blank\\\">\\u6559\\u5b66\\u8d44\\u6599\\u5e93<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u7ba1\\u7406\\u5458<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\\\" target=\\\"_blank\\\">\\u7cfb\\u7edf\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\\\" target=\\\"_blank\\\">\\u8bfe\\u7a0b\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u7ba1\\u7406<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5546\\u4e1a\\u5e94\\u7528<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\\\" target=\\\"_blank\\\">\\u4f1a\\u5458\\u4e13\\u533a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\\\" target=\\\"_blank\\\">\\u9898\\u5e93\\u589e\\u5f3a\\u7248<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5173\\u4e8e\\u6211\\u4eec<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/\\\" target=\\\"_blank\\\">ES\\u5b98\\u7f51<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\\\" target=\\\"_blank\\\">\\u5b98\\u65b9\\u5fae\\u535a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\\\" target=\\\"_blank\\\">\\u52a0\\u5165\\u6211\\u4eec<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n<\\/div>\\n\\n<div class=\\\"col-md-4 footer-logo hidden-sm hidden-xs\\\">\\n  <a class=\\\"\\\" href=\\\"http:\\/\\/www.edusoho.com\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/v2\\/img\\/bottom_logo.png?6.12.2\\\" alt=\\\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\\\"><\\/a>\\n  <div class=\\\"footer-sns\\\">\\n        <a href=\\\"http:\\/\\/weibo.com\\/edusoho\\\" target=\\\"_blank\\\"><i class=\\\"es-icon es-icon-weibo\\\"><\\/i><\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-weixin\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/weixin.png?6.12.2\\\" alt=\\\"\\\">  \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-apple\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/apple.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-android\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/android.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n      <\\/div>\\n<\\/div>\\n\\n\\n\"}','127.0.0.1',1453221539,'info'),(85,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453221602\",\"latestExecutedTime\":\"1453218002\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453221605,'info'),(86,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453221602\",\"latestExecutedTime\":\"1453218002\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453221605,'info'),(87,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453221605\",\"latestExecutedTime\":\"1453218005\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453221607,'info'),(88,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453221605\",\"latestExecutedTime\":\"1453218005\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453221607,'info'),(89,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3ec768;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453222579,'info'),(90,1,'block','update','更新编辑区#12','{\"content\":\"<section class=\\\"introduction-section\\\">\\n  <div class=\\\"container hidden-xs\\\">\\n    <div class=\\\"row\\\">\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_1.png\\\">\\n          <h4>\\u7f51\\u6821\\u529f\\u80fd\\u5f3a\\u5927<\\/h4>\\n          <h5>\\u4e00\\u4e07\\u591a\\u5bb6\\u7f51\\u6821\\u5171\\u540c\\u9009\\u62e9\\uff0c\\u503c\\u5f97\\u4fe1\\u8d56<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_2.png\\\">\\n          <h4>\\u54cd\\u5e94\\u5f0f\\u9875\\u9762\\u6280\\u672f<\\/h4>\\n          <h5>\\u91c7\\u7528\\u54cd\\u5e94\\u5f0f\\u6280\\u672f\\uff0c\\u5b8c\\u7f8e\\u9002\\u914d\\u4efb\\u610f\\u7ec8\\u7aef<\\/h5>\\n        <\\/div>\\n                                      <div class=\\\"col-md-4 col-sm-4 col-xs-12 introduction-item\\\">\\n          <img class=\\\"img-responsive\\\" src=\\\"\\/assets\\/v2\\/img\\/icon_introduction_3.png\\\">\\n          <h4>\\u6559\\u80b2\\u4e91\\u670d\\u52a1\\u652f\\u6301<\\/h4>\\n          <h5>\\u5f3a\\u529b\\u6559\\u80b2\\u4e91\\u652f\\u6301\\uff0c\\u514d\\u9664\\u4f60\\u7684\\u540e\\u987e\\u4e4b\\u5fe7<\\/h5>\\n        <\\/div>\\n          <\\/div>\\n  <\\/div>\\n<\\/section>\"}','127.0.0.1',1453222579,'info'),(91,1,'block','update','更新编辑区#13','{\"content\":\"\\n<div class=\\\"col-md-8 footer-main clearfix\\\">\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u5b66\\u751f<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/673\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u6ce8\\u518c<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/705\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u5b66\\u4e60<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/347\\/learn#lesson\\/811\\\" target=\\\"_blank\\\">\\u5982\\u4f55\\u4e92\\u52a8<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u8001\\u5e08<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/22\\\" target=\\\"_blank\\\">\\u53d1\\u5e03\\u8bfe\\u7a0b<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/147\\\" target=\\\"_blank\\\">\\u4f7f\\u7528\\u9898\\u5e93<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/372\\\" target=\\\"_blank\\\">\\u6559\\u5b66\\u8d44\\u6599\\u5e93<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item \\\">\\n  <h3>\\u6211\\u662f\\u7ba1\\u7406\\u5458<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/340\\\" target=\\\"_blank\\\">\\u7cfb\\u7edf\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/341\\\" target=\\\"_blank\\\">\\u8bfe\\u7a0b\\u8bbe\\u7f6e<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/343\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u7ba1\\u7406<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5546\\u4e1a\\u5e94\\u7528<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/358\\\" target=\\\"_blank\\\">\\u4f1a\\u5458\\u4e13\\u533a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/232\\/learn#lesson\\/467\\\" target=\\\"_blank\\\">\\u9898\\u5e93\\u589e\\u5f3a\\u7248<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.qiqiuyu.com\\/course\\/380\\\" target=\\\"_blank\\\">\\u7528\\u6237\\u5bfc\\u5165\\u5bfc\\u51fa<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n  <div class=\\\"link-item hidden-xs\\\">\\n  <h3>\\u5173\\u4e8e\\u6211\\u4eec<\\/h3>\\n  <ul>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/\\\" target=\\\"_blank\\\">ES\\u5b98\\u7f51<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/weibo.com\\/qiqiuyu\\/profile?rightmod=1&amp;wvr=6&amp;mod=personinfo\\\" target=\\\"_blank\\\">\\u5b98\\u65b9\\u5fae\\u535a<\\/a>\\n      <\\/li>\\n          <li>\\n        <a href=\\\"http:\\/\\/www.edusoho.com\\/abouts\\/joinus\\\" target=\\\"_blank\\\">\\u52a0\\u5165\\u6211\\u4eec<\\/a>\\n      <\\/li>\\n      <\\/ul>\\n<\\/div>\\n\\n<\\/div>\\n\\n<div class=\\\"col-md-4 footer-logo hidden-sm hidden-xs\\\">\\n  <a class=\\\"\\\" href=\\\"http:\\/\\/www.edusoho.com\\\" target=\\\"_blank\\\"><img src=\\\"\\/assets\\/v2\\/img\\/bottom_logo.png?6.12.2\\\" alt=\\\"\\u5efa\\u8bae\\u56fe\\u7247\\u5927\\u5c0f\\u4e3a233*64\\\"><\\/a>\\n  <div class=\\\"footer-sns\\\">\\n        <a href=\\\"http:\\/\\/weibo.com\\/edusoho\\\" target=\\\"_blank\\\"><i class=\\\"es-icon es-icon-weibo\\\"><\\/i><\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-weixin\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/weixin.png?6.12.2\\\" alt=\\\"\\\">  \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-apple\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/apple.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n            <a class=\\\"qrcode-popover top\\\">\\n      <i class=\\\"es-icon es-icon-android\\\"><\\/i>\\n      <div class=\\\"qrcode-content\\\">\\n        <img src=\\\"\\/assets\\/img\\/default\\/android.png?6.12.2\\\" alt=\\\"\\\"> \\n      <\\/div>\\n    <\\/a>\\n      <\\/div>\\n<\\/div>\\n\\n\\n\"}','127.0.0.1',1453222579,'info'),(92,1,'system','update_settings','更新侧边栏设置','{\"enabled\":\"1\"}','127.0.0.1',1453222710,'info'),(93,1,'user','login_success','登录成功','','192.168.31.133',1453263274,'info'),(94,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453225205\",\"latestExecutedTime\":\"1453221605\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453263278,'info'),(95,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453225205\",\"latestExecutedTime\":\"1453221605\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453263278,'info'),(96,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453225207\",\"latestExecutedTime\":\"1453221607\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453263302,'info'),(97,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453225207\",\"latestExecutedTime\":\"1453221607\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453263302,'info'),(98,1,'category','create','添加栏目 بىلىگ خەۋەرلىرى(#1)','{\"id\":\"1\",\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062e\\u06d5\\u06cb\\u06d5\\u0631\\u0644\\u0649\\u0631\\u0649\",\"code\":\"news\",\"weight\":\"0\",\"publishArticle\":\"1\",\"seoTitle\":\"\",\"seoKeyword\":\"\",\"seoDesc\":\"\",\"published\":\"1\",\"parentId\":\"0\",\"createdTime\":\"1453264969\"}','192.168.31.133',1453264969,'info'),(99,1,'article','create','创建文章《(بىلىگ تور دەرسخانىسى يېڭى نەشىرى پات ئارىدا سىز بىلەن يۈز كۆرىشىدۇ)》(1)','','192.168.31.133',1453265043,'info'),(100,1,'article','create','创建文章《(سەرخىل دەسلىكلەر بېكىتىمىزدە)》(2)','','192.168.31.133',1453265090,'info'),(101,1,'system','update_settings','更新站点LOGO','{\"logo\":\"files\\/system\\/2016\\/01-20\\/1248535a5546026241.png\"}','192.168.31.133',1453265334,'info'),(102,1,'system','update_settings','更新站点设置','{\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"slogan\":\"\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"url\":\"http:\\/\\/demo.edusoho.com\",\"logo\":\"files\\/system\\/2016\\/01-20\\/1248535a5546026241.png\",\"favicon\":\"\",\"seo_keywords\":\"edusoho, \\u5728\\u7ebf\\u6559\\u80b2\\u8f6f\\u4ef6, \\u5728\\u7ebf\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"seo_description\":\"edusoho\\u662f\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u5f00\\u6e90\\u8f6f\\u4ef6\",\"master_email\":\"test@edusoho.com\",\"copyright\":\"\",\"icp\":\" \\u6d59ICP\\u590713006852\\u53f7-1\",\"analytics\":\"\",\"status\":\"open\",\"closed_note\":\"\"}','192.168.31.133',1453265356,'info'),(103,1,'course','create','创建课程《بىلىگ دەرسخانىسى》(#3)','','192.168.31.133',1453265573,'info'),(104,1,'course','update','更新课程《بىلىگ دەرسخانىسى》(#3)的信息','{\"title\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"subtitle\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\\u0646\\u0649 \\u0626\\u0649\\u0634\\u0644\\u0649\\u062a\\u0649\\u0634 \\u062a\\u0648\\u063a\\u0631\\u0649\\u0633\\u0649\\u062f\\u0627\",\"expiryDay\":0,\"serializeMode\":\"none\",\"categoryId\":0,\"tags\":[1],\"buyable\":1}','192.168.31.133',1453265596,'info'),(105,1,'course','update_picture','更新课程《بىلىگ دەرسخانىسى》(#3)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125346a976c2875414.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125346a8c8a1625847.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125346a7ebae153537.jpg\"}','192.168.31.133',1453265626,'info'),(106,1,'course','publish','发布课程《بىلىگ دەرسخانىسى》(#3)','','192.168.31.133',1453265652,'info'),(107,1,'tag','create','添加标签ھەقلىق(#2)','','192.168.31.133',1453265705,'info'),(108,1,'tag','create','添加标签ھەقسىز(#3)','','192.168.31.133',1453265712,'info'),(109,1,'tag','create','添加标签يېڭى(#4)','','192.168.31.133',1453265717,'info'),(110,1,'tag','delete','编辑标签#4','','192.168.31.133',1453265733,'info'),(111,1,'course','update','更新课程《بىلىگ دەرسخانىسى》(#3)的信息','{\"title\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"subtitle\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\\u0646\\u0649 \\u0626\\u0649\\u0634\\u0644\\u0649\\u062a\\u0649\\u0634 \\u062a\\u0648\\u063a\\u0631\\u0649\\u0633\\u0649\\u062f\\u0627\",\"expiryDay\":0,\"serializeMode\":\"none\",\"categoryId\":0,\"tags\":[3],\"buyable\":1}','192.168.31.133',1453265747,'info'),(112,1,'course','update_picture','更新课程《Java ئاساسىي بىلىم دەرسلىكى》(#2)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-20\\/12563640ba51178828.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125635400db1719941.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/1256353ea396938080.jpg\"}','192.168.31.133',1453265796,'info'),(113,1,'course','update_picture','更新课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125731b3e9d3120793.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125731b33955868742.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125730addd88767016.jpg\"}','192.168.31.133',1453265851,'info'),(114,1,'course','create','创建课程《تېز سۈرەتتە توربېكەت قۇرۇش》(#4)','','192.168.31.133',1453265889,'info'),(115,1,'course','update_picture','更新课程《تېز سۈرەتتە توربېكەت قۇرۇش》(#4)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-20\\/12584993e6a3908387.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/125849936430493920.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-20\\/12584992e2ef958165.jpg\"}','192.168.31.133',1453265929,'info'),(116,1,'course','publish','发布课程《تېز سۈرەتتە توربېكەت قۇرۇش》(#4)','','192.168.31.133',1453265944,'info'),(117,1,'user','login_success','登录成功','','127.0.0.1',1453266179,'info'),(118,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453266878\",\"latestExecutedTime\":\"1453263278\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453266917,'info'),(119,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453266878\",\"latestExecutedTime\":\"1453263278\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453266917,'info'),(120,1,'user','login_success','登录成功','','192.168.31.133',1453274664,'info'),(121,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453266902\",\"latestExecutedTime\":\"1453263302\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453274666,'info'),(122,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453266902\",\"latestExecutedTime\":\"1453263302\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453274666,'info'),(123,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453270517\",\"latestExecutedTime\":\"1453266917\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453274679,'info'),(124,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453270517\",\"latestExecutedTime\":\"1453266917\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453274680,'info'),(125,1,'info','navigation_create','创建导航دەرسلىك','','192.168.31.133',1453275728,'info'),(126,1,'info','navigation_create','创建导航ئالاقىىشىڭ','','192.168.31.133',1453275743,'info'),(127,1,'info','navigation_create','创建导航بىلىگ','','192.168.31.133',1453275900,'info'),(128,1,'category','update','编辑分类 دەرسلىكلەر(#2)','{\"name\":\"\\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u06d5\\u0631\",\"weight\":\"100\",\"code\":\"default\",\"description\":\"\",\"icon\":\"\",\"parentId\":0,\"groupId\":2}','192.168.31.133',1453275944,'info'),(129,1,'category','create','添加分类 تىل دەرسلىكلىرى(#3)','{\"id\":\"3\",\"code\":\"php\",\"name\":\"\\u062a\\u0649\\u0644 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u0649\\u0631\\u0649\",\"icon\":\"\",\"path\":\"\",\"weight\":\"0\",\"groupId\":\"2\",\"parentId\":\"0\",\"description\":\"\"}','192.168.31.133',1453275981,'info'),(130,1,'category','create','添加分类 تىل ئۆگۈنۈش(#4)','{\"id\":\"4\",\"code\":\"java\",\"name\":\"\\u062a\\u0649\\u0644 \\u0626\\u06c6\\u06af\\u06c8\\u0646\\u06c8\\u0634\",\"icon\":\"\",\"path\":\"\",\"weight\":\"0\",\"groupId\":\"2\",\"parentId\":\"2\",\"description\":\"\"}','192.168.31.133',1453276028,'info'),(131,1,'system','update_settings','更新站点LOGO','{\"logo\":\"files\\/system\\/2016\\/01-20\\/154932cb1b39273904.png\"}','192.168.31.133',1453276173,'info'),(132,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_net.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453276249,'info'),(133,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_app.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453276270,'info'),(134,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/themes\\/biligcc\\/img\\/banner_eweek.jpg\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453276294,'info'),(135,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453276315,'info'),(136,1,'user','login_success','登录成功','','127.0.0.1',1453277993,'info'),(137,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div class=\\\"container\\\">\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453278031,'info'),(138,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453278050,'info'),(139,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #02987c;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453278052,'info'),(140,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453278266\",\"latestExecutedTime\":\"1453274666\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453278919,'info'),(141,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453278266\",\"latestExecutedTime\":\"1453274666\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453278919,'info'),(142,1,'user','login_success','登录成功','','192.168.31.133',1453279236,'info'),(143,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453278280\",\"latestExecutedTime\":\"1453274680\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453279236,'info'),(144,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453278280\",\"latestExecutedTime\":\"1453274680\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453279236,'info'),(145,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #0984f7;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453280271,'info'),(146,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #3b4250;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453280313,'info'),(147,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155108c32177254251.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','192.168.31.133',1453280346,'info'),(148,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453282519\",\"latestExecutedTime\":\"1453278919\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453286578,'info'),(149,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453282519\",\"latestExecutedTime\":\"1453278919\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453286578,'info'),(150,1,'user','login_success','登录成功','','192.168.31.133',1453286701,'info'),(151,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453282836\",\"latestExecutedTime\":\"1453279236\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453286702,'info'),(152,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453282836\",\"latestExecutedTime\":\"1453279236\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453286702,'info'),(153,1,'user','login_success','登录成功','','127.0.0.1',1453287196,'info'),(154,1,'user','login_success','登录成功','','127.0.0.1',1453294243,'info'),(155,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453290178\",\"latestExecutedTime\":\"1453286578\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453294243,'info'),(156,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453290178\",\"latestExecutedTime\":\"1453286578\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453294244,'info'),(157,1,'user','login_success','登录成功','','192.168.31.133',1453360206,'info'),(158,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453290302\",\"latestExecutedTime\":\"1453286702\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453360210,'info'),(159,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453290302\",\"latestExecutedTime\":\"1453286702\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453360210,'info'),(160,1,'user','login_success','登录成功','','192.168.31.133',1453363775,'info'),(161,1,'user','login_success','登录成功','','127.0.0.1',1453364422,'info'),(162,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453297844\",\"latestExecutedTime\":\"1453294244\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453364424,'info'),(163,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453297844\",\"latestExecutedTime\":\"1453294244\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453364424,'info'),(164,1,'user','login_success','登录成功','','127.0.0.1',1453366415,'info'),(165,1,'user','login_success','登录成功','','192.168.31.133',1453434996,'info'),(166,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453363810\",\"latestExecutedTime\":\"1453360210\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453435000,'info'),(167,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453363810\",\"latestExecutedTime\":\"1453360210\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','192.168.31.133',1453435000,'info'),(168,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453368024\",\"latestExecutedTime\":\"1453364424\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453953694,'info'),(169,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453368024\",\"latestExecutedTime\":\"1453364424\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453953694,'info'),(170,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453438600\",\"latestExecutedTime\":\"1453435000\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453953697,'info'),(171,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453438600\",\"latestExecutedTime\":\"1453435000\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453953697,'info'),(172,1,'user','login_success','登录成功','','127.0.0.1',1453953770,'info'),(173,1,'system','update_settings','更新站点LOGO','{\"logo\":\"files\\/system\\/2016\\/01-28\\/120315364bd7905621.png\"}','127.0.0.1',1453953795,'info'),(174,1,'system','update_settings','更新浏览器图标','{\"favicon\":\"files\\/system\\/2016\\/01-28\\/120322a6c427023260.png\"}','127.0.0.1',1453953802,'info'),(175,1,'system','update_settings','更新站点设置','{\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"slogan\":\"\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"url\":\"http:\\/\\/demo.edusoho.com\",\"file\":\"\",\"logo\":\"files\\/system\\/2016\\/01-28\\/120315364bd7905621.png\",\"favicon\":\"files\\/system\\/2016\\/01-28\\/120322a6c427023260.png\",\"seo_keywords\":\"edusoho, \\u5728\\u7ebf\\u6559\\u80b2\\u8f6f\\u4ef6, \\u5728\\u7ebf\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"seo_description\":\"edusoho\\u662f\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u5f00\\u6e90\\u8f6f\\u4ef6\",\"master_email\":\"test@edusoho.com\",\"copyright\":\"\",\"icp\":\" \\u6d59ICP\\u590713006852\\u53f7-1\",\"analytics\":\"\",\"status\":\"open\",\"closed_note\":\"\"}','127.0.0.1',1453953806,'info'),(176,1,'system','update_settings','移除站点LOGO','','127.0.0.1',1453953816,'info'),(177,1,'system','update_settings','移除站点浏览器图标','','127.0.0.1',1453953819,'info'),(178,1,'system','update_settings','更新站点LOGO','{\"logo\":\"files\\/system\\/2016\\/01-28\\/120343f08ab0237104.png\"}','127.0.0.1',1453953823,'info'),(179,1,'system','update_settings','更新站点设置','{\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"slogan\":\"\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"url\":\"http:\\/\\/demo.edusoho.com\",\"file\":\"\",\"logo\":\"files\\/system\\/2016\\/01-28\\/120315364bd7905621.png\",\"favicon\":\"files\\/system\\/2016\\/01-28\\/120322a6c427023260.png\",\"seo_keywords\":\"edusoho, \\u5728\\u7ebf\\u6559\\u80b2\\u8f6f\\u4ef6, \\u5728\\u7ebf\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"seo_description\":\"edusoho\\u662f\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u5f00\\u6e90\\u8f6f\\u4ef6\",\"master_email\":\"test@edusoho.com\",\"copyright\":\"\",\"icp\":\" \\u6d59ICP\\u590713006852\\u53f7-1\",\"analytics\":\"\",\"status\":\"open\",\"closed_note\":\"\"}','127.0.0.1',1453953886,'info'),(180,1,'system','update_settings','移除站点LOGO','','127.0.0.1',1453954021,'info'),(181,1,'system','update_settings','移除站点浏览器图标','','127.0.0.1',1453954024,'info'),(182,1,'system','update_settings','更新站点LOGO','{\"logo\":\"files\\/system\\/2016\\/01-28\\/120743fc8304922262.png\"}','127.0.0.1',1453954063,'info'),(183,1,'user','login_success','登录成功','','127.0.0.1',1453954174,'info'),(184,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155132410ca5833497.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453954749,'info'),(185,1,'system','update_settings','更新浏览器图标','{\"favicon\":\"files\\/system\\/2016\\/01-28\\/1221426c3d56265089.png\"}','127.0.0.1',1453954902,'info'),(186,1,'system','update_settings','更新站点设置','{\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"slogan\":\"\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"url\":\"http:\\/\\/demo.edusoho.com\",\"file\":\"\",\"logo\":\"files\\/system\\/2016\\/01-28\\/120743fc8304922262.png\",\"favicon\":\"files\\/system\\/2016\\/01-28\\/1221426c3d56265089.png\",\"seo_keywords\":\"edusoho, \\u5728\\u7ebf\\u6559\\u80b2\\u8f6f\\u4ef6, \\u5728\\u7ebf\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"seo_description\":\"edusoho\\u662f\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u5f00\\u6e90\\u8f6f\\u4ef6\",\"master_email\":\"test@edusoho.com\",\"copyright\":\"\",\"icp\":\" \\u6d59ICP\\u590713006852\\u53f7-1\",\"analytics\":\"\",\"status\":\"open\",\"closed_note\":\"\"}','127.0.0.1',1453954905,'info'),(187,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-20\\/155153907728837252.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453954935,'info'),(188,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453954944,'info'),(189,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453954954,'info'),(190,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122222e4b3e7666048.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453954957,'info'),(191,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/1218582975f9257067.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453955007,'info'),(192,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12233468c3d4630561.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12221353e4dd457950.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453955016,'info'),(193,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/12233468c3d4630561.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122359f4e374523128.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453955042,'info'),(194,1,'block','update','更新编辑区#11','{\"content\":\"<section class=\\\"es-poster swiper-container\\\">\\n  <div class=\\\"swiper-wrapper\\\">\\n                            <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #ff9c00;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122410a88557276811.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #59b2ac;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122359f4e374523128.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                          <div class=\\\"swiper-slide swiper-hidden\\\" style=\\\"background: #a8e6d9;\\\">\\n            <div >\\n              <a href=\\\"\\\" target=\\\"_blank\\\" ><img class=\\\"img-responsive\\\" src=\\\"\\/files\\/system\\/2016\\/01-28\\/122325d98b0e172705.jpg?6.12.2\\\">\\n              <\\/a>\\n            <\\/div>\\n          <\\/div>\\n                                                                      <\\/div>\\n  <div class=\\\"swiper-pager\\\"><\\/div>\\n<\\/section>\"}','127.0.0.1',1453955052,'info'),(195,1,'course','update_picture','更新课程《تېز سۈرەتتە توربېكەت قۇرۇش》(#4)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-28\\/122517d69a0d768881.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/122517d69048126646.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/122517d68574758387.jpg\"}','127.0.0.1',1453955117,'info'),(196,1,'course','update_picture','更新课程《بىلىگ دەرسخانىسى》(#3)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-28\\/12255319a227809425.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/122553199627737240.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/122553198492933043.jpg\"}','127.0.0.1',1453955153,'info'),(197,1,'course','update_picture','更新课程《Java ئاساسىي بىلىم دەرسلىكى》(#2)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226179372c5216983.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226179368ee062619.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226179349cd199687.jpg\"}','127.0.0.1',1453955177,'info'),(198,1,'course','update_picture','更新课程《بىلىگ تور دەرسخانىسى دەرسلىك ئىشلەش دەرسلىكى》(#1)图片','{\"smallPicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226488b0101931493.jpg\",\"middlePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226488af4f1156285.jpg\",\"largePicture\":\"public:\\/\\/default\\/2016\\/01-28\\/1226488ae2a7205388.jpg\"}','127.0.0.1',1453955208,'info'),(199,1,'user','login_success','登录成功','','127.0.0.1',1453959412,'info'),(200,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453957294\",\"latestExecutedTime\":\"1453953694\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453959413,'info'),(201,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453957294\",\"latestExecutedTime\":\"1453953694\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453959413,'info'),(202,1,'user','login_success','登录成功','','127.0.0.1',1453963971,'info'),(203,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453957297\",\"latestExecutedTime\":\"1453953697\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453963971,'info'),(204,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453957297\",\"latestExecutedTime\":\"1453953697\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453963971,'info'),(205,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453963013\",\"latestExecutedTime\":\"1453959413\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453963978,'info'),(206,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453963013\",\"latestExecutedTime\":\"1453959413\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453963978,'info'),(207,1,'user','login_success','登录成功','','127.0.0.1',1453967712,'info'),(208,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453967571\",\"latestExecutedTime\":\"1453963971\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453967713,'info'),(209,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453967571\",\"latestExecutedTime\":\"1453963971\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453967713,'info'),(210,1,'user','login_success','登录成功','','127.0.0.1',1453969610,'info'),(211,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453967578\",\"latestExecutedTime\":\"1453963978\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453969611,'info'),(212,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453967578\",\"latestExecutedTime\":\"1453963978\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453969611,'info'),(213,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453971313\",\"latestExecutedTime\":\"1453967713\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453971476,'info'),(214,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453971313\",\"latestExecutedTime\":\"1453967713\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453971476,'info'),(215,1,'user','login_success','登录成功','','127.0.0.1',1453974614,'info'),(216,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453973211\",\"latestExecutedTime\":\"1453969611\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453974615,'info'),(217,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453973211\",\"latestExecutedTime\":\"1453969611\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1453974615,'info'),(218,1,'user','login_success','登录成功','','127.0.0.1',1454044300,'info'),(219,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453975076\",\"latestExecutedTime\":\"1453971476\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454044301,'info'),(220,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453975076\",\"latestExecutedTime\":\"1453971476\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454044301,'info'),(221,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453978215\",\"latestExecutedTime\":\"1453974615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454044305,'info'),(222,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1453978215\",\"latestExecutedTime\":\"1453974615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454044305,'info'),(223,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454044316,'info'),(224,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454047901\",\"latestExecutedTime\":\"1454044301\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454053170,'info'),(225,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454047901\",\"latestExecutedTime\":\"1454044301\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454053170,'info'),(226,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454047905\",\"latestExecutedTime\":\"1454044305\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454060113,'info'),(227,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454047905\",\"latestExecutedTime\":\"1454044305\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454060113,'info'),(228,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454056770\",\"latestExecutedTime\":\"1454053170\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454060115,'info'),(229,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454056770\",\"latestExecutedTime\":\"1454053170\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454060115,'info'),(230,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454063713\",\"latestExecutedTime\":\"1454060113\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454130864,'info'),(231,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454063713\",\"latestExecutedTime\":\"1454060113\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454130864,'info'),(232,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454063715\",\"latestExecutedTime\":\"1454060115\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454130866,'info'),(233,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454063715\",\"latestExecutedTime\":\"1454060115\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454130866,'info'),(234,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454134464\",\"latestExecutedTime\":\"1454130864\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454295611,'info'),(235,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454134464\",\"latestExecutedTime\":\"1454130864\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454295611,'info'),(236,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454134466\",\"latestExecutedTime\":\"1454130866\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454295615,'info'),(237,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454134466\",\"latestExecutedTime\":\"1454130866\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454295615,'info'),(238,1,'user','login_success','登录成功','','127.0.0.1',1454297480,'info'),(239,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454299211\",\"latestExecutedTime\":\"1454295611\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454300315,'info'),(240,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454299211\",\"latestExecutedTime\":\"1454295611\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454300315,'info'),(241,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454299215\",\"latestExecutedTime\":\"1454295615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454309770,'info'),(242,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454299215\",\"latestExecutedTime\":\"1454295615\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454309770,'info'),(243,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454303915\",\"latestExecutedTime\":\"1454300315\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454309775,'info'),(244,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454303915\",\"latestExecutedTime\":\"1454300315\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454309775,'info'),(245,1,'user','login_success','登录成功','','127.0.0.1',1454309791,'info'),(246,1,'user','login_success','登录成功','','127.0.0.1',1454311430,'info'),(247,1,'user','login_success','登录成功','','127.0.0.1',1454311639,'info'),(248,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454313370\",\"latestExecutedTime\":\"1454309770\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454314045,'info'),(249,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454313370\",\"latestExecutedTime\":\"1454309770\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454314045,'info'),(250,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454313375\",\"latestExecutedTime\":\"1454309775\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454314085,'info'),(251,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454313375\",\"latestExecutedTime\":\"1454309775\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454314085,'info'),(252,1,'system','update_settings','更新站点设置','{\"name\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"slogan\":\"\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"url\":\"http:\\/\\/demo.edusoho.com\",\"file\":\"\",\"logo\":\"files\\/system\\/2016\\/01-28\\/120743fc8304922262.png\",\"favicon\":\"files\\/system\\/2016\\/01-28\\/1221426c3d56265089.png\",\"seo_keywords\":\"edusoho, \\u5728\\u7ebf\\u6559\\u80b2\\u8f6f\\u4ef6, \\u5728\\u7ebf\\u5728\\u7ebf\\u6559\\u80b2\\u89e3\\u51b3\\u65b9\\u6848\",\"seo_description\":\"edusoho\\u662f\\u5f3a\\u5927\\u7684\\u5728\\u7ebf\\u6559\\u80b2\\u5f00\\u6e90\\u8f6f\\u4ef6\",\"master_email\":\"test@edusoho.com\",\"copyright\":\"\\u5fc5\\u5229\\u7f51\\u7edc\",\"icp\":\" \\u6d59ICP\\u590713006852\\u53f7-1\",\"analytics\":\"\",\"status\":\"open\",\"closed_note\":\"\"}','127.0.0.1',1454317109,'info'),(253,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454317645\",\"latestExecutedTime\":\"1454314045\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454318354,'info'),(254,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454317645\",\"latestExecutedTime\":\"1454314045\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454318354,'info'),(255,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454317685\",\"latestExecutedTime\":\"1454314085\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454318358,'info'),(256,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454317685\",\"latestExecutedTime\":\"1454314085\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454318358,'info'),(257,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454321954\",\"latestExecutedTime\":\"1454318354\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454322096,'info'),(258,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454321954\",\"latestExecutedTime\":\"1454318354\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454322096,'info'),(259,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454321958\",\"latestExecutedTime\":\"1454318358\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454322757,'info'),(260,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454321958\",\"latestExecutedTime\":\"1454318358\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454322757,'info'),(261,1,'user','login_success','登录成功','','127.0.0.1',1454330723,'info'),(262,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454325696\",\"latestExecutedTime\":\"1454322096\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454330723,'info'),(263,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454325696\",\"latestExecutedTime\":\"1454322096\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454330723,'info'),(264,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454326357\",\"latestExecutedTime\":\"1454322757\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454330822,'info'),(265,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454326357\",\"latestExecutedTime\":\"1454322757\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454330822,'info'),(266,1,'user','login_success','登录成功','','127.0.0.1',1454334755,'info'),(267,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454334323\",\"latestExecutedTime\":\"1454330723\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454334756,'info'),(268,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454334323\",\"latestExecutedTime\":\"1454330723\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454334756,'info'),(269,1,'user','login_success','登录成功','','127.0.0.1',1454338287,'info'),(270,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454334422\",\"latestExecutedTime\":\"1454330822\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454338288,'info'),(271,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454334422\",\"latestExecutedTime\":\"1454330822\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454338288,'info'),(272,1,'user','login_success','登录成功','','127.0.0.1',1454341222,'info'),(273,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454338356\",\"latestExecutedTime\":\"1454334756\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454341223,'info'),(274,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454338356\",\"latestExecutedTime\":\"1454334756\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454341223,'info'),(275,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454341888\",\"latestExecutedTime\":\"1454338288\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454574360,'info'),(276,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454341888\",\"latestExecutedTime\":\"1454338288\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454574360,'info'),(277,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454344823\",\"latestExecutedTime\":\"1454341223\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454574373,'info'),(278,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454344823\",\"latestExecutedTime\":\"1454341223\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454574373,'info'),(279,1,'user','login_success','登录成功','','127.0.0.1',1454576146,'info'),(280,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454577960\",\"latestExecutedTime\":\"1454574360\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454581540,'info'),(281,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454577960\",\"latestExecutedTime\":\"1454574360\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454581540,'info'),(282,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454577973\",\"latestExecutedTime\":\"1454574373\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454581543,'info'),(283,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454577973\",\"latestExecutedTime\":\"1454574373\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454581543,'info'),(284,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454585140\",\"latestExecutedTime\":\"1454581540\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454590160,'info'),(285,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454585140\",\"latestExecutedTime\":\"1454581540\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454590160,'info'),(286,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454585143\",\"latestExecutedTime\":\"1454581543\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454590861,'info'),(287,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454585143\",\"latestExecutedTime\":\"1454581543\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454590861,'info'),(288,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591371,'info'),(289,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591373,'info'),(290,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591375,'info'),(291,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591382,'info'),(292,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591389,'info'),(293,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591407,'info'),(294,0,'user','login_fail','用户(IP: 127.0.0.1)，登录失败','','127.0.0.1',1454591412,'info'),(295,1,'user','login_success','登录成功','','127.0.0.1',1454591417,'info'),(296,1,'user','login_success','登录成功','','127.0.0.1',1454597420,'info'),(297,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454593760\",\"latestExecutedTime\":\"1454590160\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454597421,'info'),(298,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454593760\",\"latestExecutedTime\":\"1454590160\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454597421,'info'),(299,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454594461\",\"latestExecutedTime\":\"1454590861\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454597430,'info'),(300,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454594461\",\"latestExecutedTime\":\"1454590861\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454597430,'info'),(301,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454601021\",\"latestExecutedTime\":\"1454597421\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454642329,'info'),(302,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454601021\",\"latestExecutedTime\":\"1454597421\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454642329,'info'),(303,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454601030\",\"latestExecutedTime\":\"1454597430\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454642333,'info'),(304,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454601030\",\"latestExecutedTime\":\"1454597430\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454642333,'info'),(305,1,'user','login_success','登录成功','','127.0.0.1',1454642337,'info'),(306,1,'setting','email-verify','邮箱验证邮件发送失败:Connection could not be established with host smtp.exmail.qq.com [php_network_getaddresses: getaddrinfo failed: Name or service not known #0]','','127.0.0.1',1454642380,'error'),(307,1,'user','login_success','登录成功','','127.0.0.1',1454645937,'info'),(308,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454645929\",\"latestExecutedTime\":\"1454642329\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454645938,'info'),(309,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454645929\",\"latestExecutedTime\":\"1454642329\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454645938,'info'),(310,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454645933\",\"latestExecutedTime\":\"1454642333\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454645942,'info'),(311,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454645933\",\"latestExecutedTime\":\"1454642333\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454645942,'info'),(312,1,'system','update_settings','更新Coin虚拟币设置','{\"coin_name\":\"\\u865a\\u62df\\u5e01\",\"coin_picture\":\"\",\"coin_picture_50_50\":\"\",\"coin_picture_30_30\":\"\",\"coin_picture_20_20\":\"\",\"coin_picture_10_10\":\"\",\"cash_rate\":\"10\",\"coin_enabled\":\"1\",\"cash_model\":\"deduction\",\"charge_coin_enabled\":\"0\",\"coin_content\":\"\"}','127.0.0.1',1454646153,'info'),(313,1,'user','login_success','登录成功','','127.0.0.1',1454729931,'info'),(314,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454649538\",\"latestExecutedTime\":\"1454645938\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454729931,'info'),(315,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454649538\",\"latestExecutedTime\":\"1454645938\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454729931,'info'),(316,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454649542\",\"latestExecutedTime\":\"1454645942\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454729937,'info'),(317,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454649542\",\"latestExecutedTime\":\"1454645942\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1454729937,'info'),(318,1,'user','login_success','登录成功','','127.0.0.1',1455101136,'info'),(319,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454733531\",\"latestExecutedTime\":\"1454729931\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455101137,'info'),(320,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454733531\",\"latestExecutedTime\":\"1454729931\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455101137,'info'),(321,1,'user','login_success','登录成功','','127.0.0.1',1455118604,'info'),(322,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454733537\",\"latestExecutedTime\":\"1454729937\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455118605,'info'),(323,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1454733537\",\"latestExecutedTime\":\"1454729937\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455118605,'info'),(324,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455104737\",\"latestExecutedTime\":\"1455101137\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455118608,'info'),(325,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455104737\",\"latestExecutedTime\":\"1455101137\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455118608,'info'),(326,0,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455122205\",\"latestExecutedTime\":\"1455118605\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455192823,'info'),(327,0,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455122205\",\"latestExecutedTime\":\"1455118605\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455192823,'info'),(328,0,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455122208\",\"latestExecutedTime\":\"1455118608\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455192840,'info'),(329,0,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455122208\",\"latestExecutedTime\":\"1455118608\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455192840,'info'),(330,1,'user','login_success','登录成功','','127.0.0.1',1455418689,'info'),(331,1,'crontab','job_start','定时任务(#2)开始执行！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455196423\",\"latestExecutedTime\":\"1455192823\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455418693,'info'),(332,1,'crontab','job_end','定时任务(#2)执行结束！','{\"id\":\"2\",\"name\":\"DeleteExpiredTokenJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\User\\\\Job\\\\DeleteExpiredTokenJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455196423\",\"latestExecutedTime\":\"1455192823\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455418693,'info'),(333,1,'crontab','job_start','定时任务(#1)开始执行！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455196440\",\"latestExecutedTime\":\"1455192840\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455419021,'info'),(334,1,'crontab','job_end','定时任务(#1)执行结束！','{\"id\":\"1\",\"name\":\"CancelOrderJob\",\"cycle\":\"everyhour\",\"cycleTime\":\"0\",\"jobClass\":\"Topxia\\\\Service\\\\Order\\\\Job\\\\CancelOrderJob\",\"jobParams\":null,\"targetType\":\"\",\"targetId\":\"0\",\"executing\":\"0\",\"nextExcutedTime\":\"1455196440\",\"latestExecutedTime\":\"1455192840\",\"creatorId\":\"0\",\"createdTime\":\"0\"}','127.0.0.1',1455419021,'info');
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marker`
--

DROP TABLE IF EXISTS `marker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `second` int(10) unsigned NOT NULL COMMENT '驻点时间',
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='驻点';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marker`
--

LOCK TABLES `marker` WRITE;
/*!40000 ALTER TABLE `marker` DISABLE KEYS */;
/*!40000 ALTER TABLE `marker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '私信类型',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_conversation`
--

DROP TABLE IF EXISTS `message_conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
  `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后一条信息，用Json显示',
  `latestMessageTime` int(10) unsigned NOT NULL,
  `latestMessageContent` text NOT NULL,
  `latestMessageType` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '最后一条私信类型',
  `unreadNum` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_conversation`
--

LOCK TABLES `message_conversation` WRITE;
/*!40000 ALTER TABLE `message_conversation` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_conversation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_relation`
--

DROP TABLE IF EXISTS `message_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对话id',
  `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息Id',
  `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0表示未读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_relation`
--

LOCK TABLES `message_relation` WRITE;
/*!40000 ALTER TABLE `message_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20131216214116'),('20131216214117'),('20131218143135'),('20131219223817'),('20131220004943'),('20131220094752'),('20131224113116'),('20131224141953'),('20131225141008'),('20131227163057'),('20140102092245'),('20140102104518'),('20140103162841'),('20140106102940'),('20140106103708'),('20140106153325'),('20140106154042'),('20140106230422'),('20140107150319'),('20140108103543'),('20140108114315'),('20140108164043'),('20140109160220'),('20140109164606'),('20140110094411'),('20140114003255'),('20140114174132'),('20140114213421'),('20140116163245'),('20140116173341'),('20140118225821'),('20140120105025'),('20140120155041'),('20140120203857'),('20140120204209'),('20140121204639'),('20140121212211'),('20140122015531'),('20140122151850'),('20140122152535'),('20140123204503'),('20140123235827'),('20140124021614'),('20140124023325'),('20140124100906'),('20140124121006'),('20140125203025'),('20140128000545'),('20140129211403'),('20140207153820'),('20140210144225'),('20140210225215'),('20140210232103'),('20140213150851'),('20140213152158'),('20140217174105'),('20140217211222'),('20140219010813'),('20140219102205'),('20140304023514'),('20140306105759'),('20140311153601'),('20140311154750'),('20140311155608'),('20140312222056'),('20140312223328'),('20140312223712'),('20140318165049'),('20140318171410'),('20140318172534'),('20140324165812'),('20140325115258'),('20140325131740'),('20140325145027'),('20140325152748'),('20140325171419'),('20140325222128'),('20140326144430'),('20140327132559'),('20140331012614'),('20140401114137'),('20140401220029'),('20140402123816'),('20140402162116'),('20140403100017'),('20140403200550'),('20140403205346'),('20140404173832'),('20140409131043'),('20140409154117'),('20140412225621'),('20140414160731'),('20140415102827'),('20140415211525'),('20140415230024'),('20140415234431'),('20140421220025'),('20140421221701'),('20140421222411'),('20140505115335'),('20140505143532'),('20140505144428'),('20140512214159'),('20140513001919'),('20140513142326'),('20140515141107'),('20140515141501'),('20140522101058'),('20140528101559'),('20140605232507'),('20140606002023'),('20140623005131'),('20140626155702'),('20140630081610'),('20140708220626'),('20140709090951'),('20140709233702'),('20140710143535'),('20140714172254'),('20140716091348'),('20140716110046'),('20140717093105'),('20140719164027'),('20140720060736'),('20140720223611'),('20140721002517'),('20140721142005'),('20140724121920'),('20140801145736'),('20140802111806'),('20140807104518'),('20140812212256'),('20140813202042'),('20140813224947'),('20140816115158'),('20140822142549'),('20140829082506'),('20140829183638'),('20140831004620'),('20140915172542'),('20140918174516'),('20140923163345'),('20140924144730'),('20141020110852'),('20141023114246'),('20141028095644'),('20141028195545'),('20141106130441'),('20141106142626'),('20141119145748'),('20141120161210'),('20141125091145'),('20141202104230'),('20141202141658'),('20141206103015'),('20141208094934'),('20141211112852'),('20141216171811'),('20141218110955'),('20141219142613'),('20141223221407'),('20141224130707'),('20141226111814'),('20141226150032'),('20150108135808'),('20150108151021'),('20150113230519'),('20150123161655'),('20150127114241'),('20150128092739'),('20150130160105'),('20150130163935'),('20150203101202'),('20150204152700'),('20150206145619'),('20150210100254'),('20150210165909'),('20150212105853'),('20150212203655'),('20150225183417'),('20150226141602'),('20150227095640'),('20150304085847'),('20150313155930'),('20150317153836'),('20150319142106'),('20150326104634'),('20150330215441'),('20150331103255'),('20150331104946'),('20150331115246'),('20150401143841'),('20150401183607'),('20150402094005'),('20150402104237'),('20150407115845'),('20150408093736'),('20150410180226'),('20150412211039'),('20150414173841'),('20150415103250'),('20150416005722'),('20150416111909'),('20150420095839'),('20150420154147'),('20150423164649'),('20150424171555'),('20150427152129'),('20150428164013'),('20150513181253'),('20150515120030'),('20150515140331'),('20150515160712'),('20150518161524'),('20150518163356'),('20150519141733'),('20150519151803'),('20150520140504'),('20150520165734'),('20150520190401'),('20150521140603'),('20150522094750'),('20150522124301'),('20150525104917'),('20150526142159'),('20150604233338'),('20150609221436'),('20150618194042'),('20150630200940'),('20150706112110'),('20150716162756'),('20150806163521'),('20150812094914'),('20150812102932'),('20150812172226'),('20150818192323'),('20150819091720'),('20150819092859'),('20150828182832'),('20150908161055'),('20150909163344'),('20150910143701'),('20150916193947'),('20150918112009'),('20150925151710'),('20151009134509'),('20151010104630'),('20151012101047'),('20151012173209'),('20151016103829'),('20151019105953'),('20151019150102'),('20151022152630'),('20151022200931'),('20151026152411'),('20151028110310'),('20151028135212'),('20151104134308'),('20151105115345'),('20151106102555'),('20151111153515'),('20151118152831'),('20151126102525'),('20151201124025'),('20151204163654'),('20151208194721'),('20151210151653'),('20151211160108'),('20151214172845'),('20151214212530'),('20151217164502'),('20151217172230'),('20151222161005'),('20151225161537'),('20160111172145'),('20160112171411'),('20160119135346'),('20160121172321');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_device`
--

DROP TABLE IF EXISTS `mobile_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设备ID',
  `imei` varchar(255) NOT NULL COMMENT '串号',
  `platform` varchar(255) NOT NULL COMMENT '平台',
  `version` varchar(255) NOT NULL COMMENT '版本',
  `screenresolution` varchar(100) NOT NULL COMMENT '分辨率',
  `kernel` varchar(255) NOT NULL COMMENT '内核',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_device`
--

LOCK TABLES `mobile_device` WRITE;
/*!40000 ALTER TABLE `mobile_device` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `money_card`
--

DROP TABLE IF EXISTS `money_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `money_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardId` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `deadline` varchar(19) NOT NULL COMMENT '有效时间',
  `rechargeTime` int(10) NOT NULL COMMENT '充值时间，0为未充值',
  `cardStatus` enum('normal','invalid','recharged','receive') NOT NULL DEFAULT 'invalid',
  `receiveTime` int(10) NOT NULL DEFAULT '0' COMMENT '领取学习卡时间',
  `rechargeUserId` int(11) NOT NULL,
  `batchId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `money_card`
--

LOCK TABLES `money_card` WRITE;
/*!40000 ALTER TABLE `money_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `money_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `money_card_batch`
--

DROP TABLE IF EXISTS `money_card_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `money_card_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardPrefix` varchar(32) NOT NULL,
  `cardLength` int(8) NOT NULL,
  `number` int(11) NOT NULL,
  `rechargedNumber` int(11) NOT NULL,
  `token` varchar(64) NOT NULL DEFAULT '0',
  `deadline` varchar(19) CHARACTER SET latin1 NOT NULL,
  `money` int(8) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdTime` int(11) NOT NULL,
  `note` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `money_card_batch`
--

LOCK TABLES `money_card_batch` WRITE;
/*!40000 ALTER TABLE `money_card_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `money_card_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `money_record`
--

DROP TABLE IF EXISTS `money_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `money_record` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `type` enum('income','payout') NOT NULL COMMENT '记录类型',
  `transactionNo` varchar(128) NOT NULL COMMENT '交易号',
  `transactionTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间/消费时间',
  `amount` float(10,2) NOT NULL COMMENT '充值金额/消费金额',
  `status` enum('created','finished') NOT NULL DEFAULT 'created',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `money_record`
--

LOCK TABLES `money_record` WRITE;
/*!40000 ALTER TABLE `money_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `money_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `navigation`
--

DROP TABLE IF EXISTS `navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `navigation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT '文案',
  `url` varchar(300) NOT NULL COMMENT 'URL',
  `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序,数字替代',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父导航ID',
  `createdTime` int(11) NOT NULL,
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL COMMENT '类型',
  `isOpen` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认1，为开启',
  `isNewWin` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认为1,另开窗口',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='导航数据表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `navigation`
--

LOCK TABLES `navigation` WRITE;
/*!40000 ALTER TABLE `navigation` DISABLE KEYS */;
INSERT INTO `navigation` VALUES (1,'باشبەت','http://biligcc.dev',1,0,1453041222,1453041222,'top',1,0),(2,'دەرسلىك','http://',2,0,1453275728,1453275728,'top',1,0),(3,'ئالاقىىشىڭ','http://',3,0,1453275743,1453275743,'top',1,0),(4,'بىلىگ','http://',1,0,1453275900,1453275900,'foot',1,0);
/*!40000 ALTER TABLE `navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'default',
  `content` text,
  `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知表中的ID',
  `createdTime` int(10) unsigned NOT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES (1,1,'global','{\"content\":\"<p>\\u0633\\u06d5\\u0631\\u062e\\u0649\\u0644 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u06d5\\u0631 \\u0628\\u06d0\\u0643\\u0649\\u062a\\u0649\\u0645\\u0649\\u0632\\u062f\\u06d5<\\/p>\\r\\n\",\"title\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\"}',1,1453264923,0);
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_log`
--

DROP TABLE IF EXISTS `order_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `message` text,
  `data` text,
  `userId` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_log`
--

LOCK TABLES `order_log` WRITE;
/*!40000 ALTER TABLE `order_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_refund`
--

DROP TABLE IF EXISTS `order_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `targetType` varchar(64) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL,
  `status` enum('created','success','failed','cancelled') NOT NULL DEFAULT 'created',
  `expectedAmount` float(10,2) unsigned DEFAULT '0.00' COMMENT '期望退款的金额，NULL代表未知，0代表不需要退款',
  `actualAmount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际退款金额，0代表无退款',
  `reasonType` varchar(64) NOT NULL DEFAULT '',
  `reasonNote` varchar(1024) NOT NULL DEFAULT '',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_refund`
--

LOCK TABLES `order_refund` WRITE;
/*!40000 ALTER TABLE `order_refund` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_refund` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(32) NOT NULL,
  `status` enum('created','paid','refunding','refunded','cancelled') NOT NULL,
  `title` varchar(255) NOT NULL,
  `targetType` varchar(64) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单实付金额',
  `totalPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `giftTo` varchar(64) NOT NULL DEFAULT '',
  `refundId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次退款操作记录的ID',
  `userId` int(10) unsigned NOT NULL,
  `coupon` varchar(255) NOT NULL DEFAULT '',
  `couponDiscount` float(10,2) NOT NULL DEFAULT '0.00',
  `payment` enum('none','alipay','tenpay','coin','wxpay','heepay','quickpay','iosiap') NOT NULL,
  `coinAmount` float(10,2) NOT NULL DEFAULT '0.00',
  `coinRate` float(10,2) DEFAULT NULL,
  `priceType` enum('RMB','Coin') NOT NULL,
  `bank` varchar(32) NOT NULL DEFAULT '' COMMENT '银行编号',
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
  `cashSn` bigint(20) DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `data` text COMMENT '订单业务数据',
  `createdTime` int(10) unsigned NOT NULL,
  `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID',
  `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣',
  `token` varchar(50) DEFAULT NULL COMMENT '令牌',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL DEFAULT '',
  `stem` text COMMENT '题干',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `answer` text COMMENT '参考答案',
  `analysis` text COMMENT '解析',
  `metas` text COMMENT '题目元信息',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
  `difficulty` varchar(64) NOT NULL DEFAULT 'normal',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `parentId` int(10) unsigned DEFAULT '0' COMMENT '材料父ID',
  `subCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子题数量',
  `finishedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成次数',
  `passedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question`
--

LOCK TABLES `question` WRITE;
/*!40000 ALTER TABLE `question` DISABLE KEYS */;
/*!40000 ALTER TABLE `question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_category`
--

DROP TABLE IF EXISTS `question_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '类别名称',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库类别表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_category`
--

LOCK TABLES `question_category` WRITE;
/*!40000 ALTER TABLE `question_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_favorite`
--

DROP TABLE IF EXISTS `question_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
  `target` varchar(255) NOT NULL DEFAULT '',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_favorite`
--

LOCK TABLES `question_favorite` WRITE;
/*!40000 ALTER TABLE `question_favorite` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_favorite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_marker`
--

DROP TABLE IF EXISTS `question_marker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_marker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
  `questionId` int(10) unsigned NOT NULL COMMENT '问题Id',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
  `stem` text COMMENT '题干',
  `answer` text COMMENT '参考答案',
  `analysis` text COMMENT '解析',
  `metas` text COMMENT '题目元信息',
  `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='弹题';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_marker`
--

LOCK TABLES `question_marker` WRITE;
/*!40000 ALTER TABLE `question_marker` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_marker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_marker_result`
--

DROP TABLE IF EXISTS `question_marker_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_marker_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
  `questionMarkerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹题ID',
  `lessonId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
  `answer` text,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_marker_result`
--

LOCK TABLES `question_marker_result` WRITE;
/*!40000 ALTER TABLE `question_marker_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_marker_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recent_post_num`
--

DROP TABLE IF EXISTS `recent_post_num`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recent_post_num` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ip` varchar(20) NOT NULL COMMENT 'IP',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recent_post_num`
--

LOCK TABLES `recent_post_num` WRITE;
/*!40000 ALTER TABLE `recent_post_num` DISABLE KEYS */;
/*!40000 ALTER TABLE `recent_post_num` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sess_id` varbinary(128) NOT NULL,
  `sess_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sess_data` blob NOT NULL,
  `sess_time` int(10) unsigned NOT NULL,
  `sess_lifetime` mediumint(9) NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('a02vjunm97dncbo062v1c6lpq2',1,'_sf2_attributes|a:2:{s:7:\"loginIp\";s:9:\"127.0.0.1\";s:14:\"_security_main\";s:2041:\"C:68:\"Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken\":1958:{a:3:{i:0;s:30:\"ThisTokenIsNotSoSecretChangeIt\";i:1;s:4:\"main\";i:2;s:1881:\"a:4:{i:0;O:31:\"Topxia\\Service\\User\\CurrentUser\":1:{s:7:\"\0*\0data\";a:38:{s:2:\"id\";s:1:\"1\";s:5:\"email\";s:16:\"test@edusoho.com\";s:14:\"verifiedMobile\";s:0:\"\";s:8:\"password\";s:44:\"avQifS3v1zYa6PYnWiTxDqmUp5U+6uH5jI99MFW0lBE=\";s:4:\"salt\";s:31:\"76pqmmj93sowkgcgsgoog4cwo088kg0\";s:11:\"payPassword\";s:0:\"\";s:15:\"payPasswordSalt\";s:0:\"\";s:3:\"uri\";s:0:\"\";s:8:\"nickname\";s:15:\"测试管理员\";s:5:\"title\";s:12:\"技术总监\";s:4:\"tags\";s:0:\"\";s:4:\"type\";s:7:\"default\";s:5:\"point\";s:1:\"0\";s:4:\"coin\";s:1:\"0\";s:11:\"smallAvatar\";s:50:\"public://default/2016/01-28/122705936fd0862024.jpg\";s:12:\"mediumAvatar\";s:50:\"public://default/2016/01-28/122705936185051189.jpg\";s:11:\"largeAvatar\";s:50:\"public://default/2016/01-28/122705934fb4341002.jpg\";s:13:\"emailVerified\";s:1:\"0\";s:5:\"setup\";s:1:\"1\";s:5:\"roles\";a:3:{i:0;s:9:\"ROLE_USER\";i:1;s:16:\"ROLE_SUPER_ADMIN\";i:2;s:12:\"ROLE_TEACHER\";}s:8:\"promoted\";s:1:\"0\";s:12:\"promotedTime\";s:1:\"0\";s:6:\"locked\";s:1:\"0\";s:20:\"lastPasswordFailTime\";s:1:\"0\";s:12:\"lockDeadline\";s:1:\"0\";s:29:\"consecutivePasswordErrorTimes\";s:1:\"0\";s:9:\"loginTime\";s:10:\"1455418689\";s:7:\"loginIp\";s:9:\"127.0.0.1\";s:14:\"loginSessionId\";s:26:\"a02vjunm97dncbo062v1c6lpq2\";s:12:\"approvalTime\";s:1:\"0\";s:14:\"approvalStatus\";s:9:\"unapprove\";s:13:\"newMessageNum\";s:1:\"0\";s:18:\"newNotificationNum\";s:1:\"0\";s:9:\"createdIp\";s:9:\"127.0.0.1\";s:11:\"createdTime\";s:10:\"1452852362\";s:11:\"updatedTime\";s:10:\"1455418689\";s:10:\"inviteCode\";N;s:9:\"currentIp\";s:9:\"127.0.0.1\";}}i:1;b:1;i:2;a:3:{i:0;O:41:\"Symfony\\Component\\Security\\Core\\Role\\Role\":1:{s:47:\"\0Symfony\\Component\\Security\\Core\\Role\\Role\0role\";s:9:\"ROLE_USER\";}i:1;O:41:\"Symfony\\Component\\Security\\Core\\Role\\Role\":1:{s:47:\"\0Symfony\\Component\\Security\\Core\\Role\\Role\0role\";s:16:\"ROLE_SUPER_ADMIN\";}i:2;O:41:\"Symfony\\Component\\Security\\Core\\Role\\Role\":1:{s:47:\"\0Symfony\\Component\\Security\\Core\\Role\\Role\0role\";s:12:\"ROLE_TEACHER\";}}i:3;a:0:{}}\";}}\";}_sf2_flashes|a:0:{}_sf2_meta|a:3:{s:1:\"u\";i:1455419045;s:1:\"c\";i:1455418689;s:1:\"l\";s:1:\"0\";}',1455419045,1800);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES (13,'contact','a:8:{s:7:\"enabled\";i:0;s:8:\"worktime\";s:12:\"9:00 - 17:00\";s:2:\"qq\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:7:\"qqgroup\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:5:\"phone\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:10:\"webchatURI\";s:0:\"\";s:5:\"email\";s:0:\"\";s:5:\"color\";s:7:\"default\";}'),(15,'mailer','a:7:{s:7:\"enabled\";i:1;s:4:\"host\";s:18:\"smtp.exmail.qq.com\";s:4:\"port\";s:2:\"25\";s:8:\"username\";s:16:\"test@edusoho.com\";s:8:\"password\";s:6:\"est123\";s:4:\"from\";s:16:\"test@edusoho.com\";s:4:\"name\";s:4:\"TEST\";}'),(18,'refund','a:4:{s:13:\"maxRefundDays\";i:10;s:17:\"applyNotification\";s:107:\"您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。\";s:19:\"successNotification\";s:82:\"您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。\";s:18:\"failedNotification\";s:93:\"您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。\";}'),(21,'post_num_rules','a:1:{s:5:\"rules\";a:2:{s:6:\"thread\";a:1:{s:14:\"fiveMuniteRule\";a:2:{s:8:\"interval\";i:300;s:7:\"postNum\";i:100;}}s:17:\"threadLoginedUser\";a:1:{s:14:\"fiveMuniteRule\";a:2:{s:8:\"interval\";i:300;s:7:\"postNum\";i:50;}}}}'),(24,'consult','a:9:{s:7:\"enabled\";s:1:\"1\";s:5:\"color\";s:7:\"default\";s:2:\"qq\";a:1:{i:0;a:3:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";s:3:\"url\";s:0:\"\";}}s:7:\"qqgroup\";a:1:{i:0;a:3:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";s:3:\"url\";s:0:\"\";}}s:8:\"worktime\";s:12:\"9:00 - 17:00\";s:5:\"phone\";a:1:{i:0;a:2:{s:4:\"name\";s:0:\"\";s:6:\"number\";s:0:\"\";}}s:4:\"file\";s:0:\"\";s:10:\"webchatURI\";s:0:\"\";s:5:\"email\";s:0:\"\";}'),(25,'user_default','a:1:{s:9:\"user_name\";s:6:\"学员\";}'),(27,'auth','a:17:{s:13:\"register_mode\";s:5:\"email\";s:13:\"email_enabled\";s:6:\"closed\";s:12:\"setting_time\";i:1453041259;s:22:\"email_activation_title\";s:33:\"请激活您的{{sitename}}帐号\";s:21:\"email_activation_body\";s:380:\"Hi, {{nickname}}\r\n\r\n欢迎加入{{sitename}}!\r\n\r\n请点击下面的链接完成注册：\r\n\r\n{{verifyurl}}\r\n\r\n如果以上链接无法点击，请将上面的地址复制到你的浏览器(如IE)的地址栏中打开，该链接地址24小时内打开有效。\r\n\r\n感谢对{{sitename}}的支持！\r\n\r\n{{sitename}} {{siteurl}}\r\n\r\n(这是一封自动产生的email，请勿回复。)\";s:15:\"welcome_enabled\";s:6:\"opened\";s:14:\"welcome_sender\";s:15:\"测试管理员\";s:15:\"welcome_methods\";a:0:{}s:13:\"welcome_title\";s:24:\"欢迎加入{{sitename}}\";s:12:\"welcome_body\";s:138:\"您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。\";s:10:\"user_terms\";s:6:\"opened\";s:15:\"user_terms_body\";s:0:\"\";s:15:\"captcha_enabled\";i:0;s:19:\"register_protective\";s:4:\"none\";s:16:\"nickname_enabled\";i:0;s:12:\"avatar_alert\";s:4:\"none\";s:10:\"_cloud_sms\";s:0:\"\";}'),(30,'storage','a:6:{s:11:\"upload_mode\";s:5:\"local\";s:16:\"cloud_api_server\";s:22:\"http://api.edusoho.net\";s:16:\"cloud_access_key\";s:0:\"\";s:12:\"cloud_bucket\";s:0:\"\";s:16:\"cloud_secret_key\";s:0:\"\";s:20:\"cloud_api_tui_server\";s:0:\"\";}'),(31,'developer','a:7:{s:5:\"debug\";s:1:\"1\";s:11:\"app_api_url\";s:0:\"\";s:16:\"cloud_api_server\";s:22:\"http://api.edusoho.net\";s:20:\"cloud_api_tui_server\";s:0:\"\";s:13:\"hls_encrypted\";s:1:\"1\";s:14:\"balloon_player\";s:1:\"1\";s:15:\"without_network\";s:1:\"0\";}'),(32,'login_bind','a:27:{s:11:\"login_limit\";s:1:\"1\";s:7:\"enabled\";s:1:\"1\";s:22:\"temporary_lock_enabled\";s:1:\"1\";s:28:\"temporary_lock_allowed_times\";s:1:\"5\";s:31:\"ip_temporary_lock_allowed_times\";s:2:\"20\";s:22:\"temporary_lock_minutes\";s:2:\"20\";s:13:\"weibo_enabled\";s:1:\"1\";s:9:\"weibo_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:12:\"weibo_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:22:\"weibo_set_fill_account\";s:1:\"1\";s:10:\"qq_enabled\";s:1:\"1\";s:6:\"qq_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:9:\"qq_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:19:\"qq_set_fill_account\";s:1:\"1\";s:14:\"renren_enabled\";s:1:\"1\";s:10:\"renren_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"renren_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:23:\"renren_set_fill_account\";s:1:\"1\";s:17:\"weixinweb_enabled\";s:1:\"1\";s:13:\"weixinweb_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"weixinweb_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:26:\"weixinweb_set_fill_account\";s:1:\"1\";s:17:\"weixinmob_enabled\";s:1:\"1\";s:13:\"weixinmob_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"weixinmob_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:26:\"weixinmob_set_fill_account\";s:1:\"1\";s:11:\"verify_code\";s:0:\"\";}'),(37,'course_default','a:2:{s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";}'),(38,'default','a:3:{s:9:\"user_name\";s:6:\"学员\";s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";}'),(47,'menu_hiddens','a:0:{}'),(48,'payment','a:19:{s:7:\"enabled\";s:1:\"1\";s:16:\"disabled_message\";s:48:\"尚未开启支付模块，无法购买课程。\";s:14:\"alipay_enabled\";s:1:\"1\";s:11:\"alipay_type\";s:6:\"direct\";s:10:\"alipay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"alipay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:14:\"alipay_account\";s:17:\"torghay@bilig.biz\";s:19:\"close_trade_enabled\";s:1:\"1\";s:13:\"wxpay_enabled\";s:1:\"1\";s:9:\"wxpay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"wxpay_account\";s:13:\"dsdasdasdasda\";s:12:\"wxpay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:14:\"heepay_enabled\";s:1:\"1\";s:10:\"heepay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:13:\"heepay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:16:\"quickpay_enabled\";s:1:\"1\";s:12:\"quickpay_key\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:15:\"quickpay_secret\";s:26:\"ddsdsadasdasdasdasaddasdas\";s:12:\"quickpay_aes\";s:26:\"ddsdsadasdasdasdasaddasdas\";}'),(54,'operation_mobile','a:0:{}'),(55,'operation_course_grids','a:0:{}'),(56,'mobile','a:10:{s:7:\"enabled\";s:1:\"1\";s:3:\"ver\";s:1:\"1\";s:5:\"about\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:6:\"notice\";s:0:\"\";s:7:\"splash1\";s:0:\"\";s:7:\"splash2\";s:0:\"\";s:7:\"splash3\";s:0:\"\";s:7:\"splash4\";s:0:\"\";s:7:\"splash5\";s:0:\"\";}'),(61,'esBar','a:1:{s:7:\"enabled\";s:1:\"1\";}'),(81,'theme','a:8:{s:4:\"code\";s:6:\"jianmo\";s:4:\"name\";s:6:\"简墨\";s:6:\"author\";s:13:\"EduSoho官方\";s:7:\"version\";s:5:\"1.0.0\";s:15:\"supprot_version\";s:6:\"6.0.0+\";s:4:\"date\";s:8:\"2015-6-1\";s:5:\"thumb\";s:13:\"img/theme.jpg\";s:3:\"uri\";s:6:\"jianmo\";}'),(82,'site','a:14:{s:4:\"name\";s:38:\"بىلىگ تور دەرسخانىسى\";s:6:\"slogan\";s:33:\"强大的在线教育解决方案\";s:3:\"url\";s:23:\"http://demo.edusoho.com\";s:4:\"file\";s:0:\"\";s:4:\"logo\";s:46:\"files/system/2016/01-28/120743fc8304922262.png\";s:7:\"favicon\";s:46:\"files/system/2016/01-28/1221426c3d56265089.png\";s:12:\"seo_keywords\";s:59:\"edusoho, 在线教育软件, 在线在线教育解决方案\";s:15:\"seo_description\";s:43:\"edusoho是强大的在线教育开源软件\";s:12:\"master_email\";s:16:\"test@edusoho.com\";s:9:\"copyright\";s:12:\"必利网络\";s:3:\"icp\";s:23:\" 浙ICP备13006852号-1\";s:9:\"analytics\";s:0:\"\";s:6:\"status\";s:4:\"open\";s:11:\"closed_note\";s:0:\"\";}'),(83,'classroom','a:3:{s:7:\"enabled\";s:1:\"1\";s:4:\"name\";s:0:\"\";s:12:\"discount_buy\";s:1:\"1\";}'),(85,'invite','a:5:{s:19:\"invite_code_setting\";s:1:\"1\";s:19:\"promoted_user_value\";s:0:\"\";s:18:\"promote_user_value\";s:0:\"\";s:8:\"deadline\";s:2:\"90\";s:25:\"inviteInfomation_template\";s:16:\"{{registerUrl}} \";}'),(88,'course','a:20:{s:23:\"welcome_message_enabled\";s:1:\"1\";s:20:\"welcome_message_body\";s:41:\"{{nickname}},欢迎加入课程{{course}}\";s:20:\"teacher_modify_price\";s:1:\"1\";s:20:\"teacher_search_order\";s:1:\"1\";s:22:\"teacher_manage_student\";s:1:\"1\";s:22:\"teacher_export_student\";s:1:\"0\";s:22:\"student_download_media\";s:1:\"0\";s:23:\"explore_default_orderBy\";s:6:\"latest\";s:14:\"relatedCourses\";s:1:\"1\";s:21:\"allowAnonymousPreview\";s:1:\"1\";s:12:\"copy_enabled\";s:1:\"1\";s:21:\"testpaperCopy_enabled\";s:1:\"1\";s:24:\"show_student_num_enabled\";s:1:\"1\";s:22:\"custom_chapter_enabled\";s:1:\"1\";s:12:\"chapter_name\";s:3:\"章\";s:9:\"part_name\";s:3:\"节\";s:14:\"userinfoFields\";a:0:{}s:22:\"userinfoFieldNameArray\";a:0:{}s:19:\"live_course_enabled\";s:1:\"1\";s:21:\"live_student_capacity\";i:0;}'),(89,'live-course','a:2:{s:19:\"live_course_enabled\";s:1:\"1\";s:21:\"live_student_capacity\";i:0;}'),(90,'coin','a:11:{s:9:\"coin_name\";s:9:\"虚拟币\";s:12:\"coin_picture\";s:0:\"\";s:18:\"coin_picture_50_50\";s:0:\"\";s:18:\"coin_picture_30_30\";s:0:\"\";s:18:\"coin_picture_20_20\";s:0:\"\";s:18:\"coin_picture_10_10\";s:0:\"\";s:9:\"cash_rate\";s:2:\"10\";s:12:\"coin_enabled\";s:1:\"1\";s:10:\"cash_model\";s:9:\"deduction\";s:19:\"charge_coin_enabled\";s:1:\"0\";s:12:\"coin_content\";s:0:\"\";}'),(91,'_app_last_check','i:1455419022;');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shortcut`
--

DROP TABLE IF EXISTS `shortcut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shortcut` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shortcut`
--

LOCK TABLES `shortcut` WRITE;
/*!40000 ALTER TABLE `shortcut` DISABLE KEYS */;
INSERT INTO `shortcut` VALUES (1,1,'课程管理 - 课程管理 - 课程 ','/admin/course/normal/index',1453276076);
/*!40000 ALTER TABLE `shortcut` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sign_card`
--

DROP TABLE IF EXISTS `sign_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `cardNum` int(10) unsigned NOT NULL DEFAULT '0',
  `useTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sign_card`
--

LOCK TABLES `sign_card` WRITE;
/*!40000 ALTER TABLE `sign_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `sign_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sign_target_statistics`
--

DROP TABLE IF EXISTS `sign_target_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_target_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `signedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到人数',
  `date` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '统计日期',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sign_target_statistics`
--

LOCK TABLES `sign_target_statistics` WRITE;
/*!40000 ALTER TABLE `sign_target_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `sign_target_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sign_user_log`
--

DROP TABLE IF EXISTS `sign_user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_user_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sign_user_log`
--

LOCK TABLES `sign_user_log` WRITE;
/*!40000 ALTER TABLE `sign_user_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `sign_user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sign_user_statistics`
--

DROP TABLE IF EXISTS `sign_user_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_user_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `keepDays` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sign_user_statistics`
--

LOCK TABLES `sign_user_statistics` WRITE;
/*!40000 ALTER TABLE `sign_user_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `sign_user_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '动态发布的人',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程Id',
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级id',
  `type` varchar(64) NOT NULL COMMENT '动态类型',
  `objectType` varchar(64) NOT NULL DEFAULT '' COMMENT '动态对象的类型',
  `objectId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态对象ID',
  `message` text NOT NULL COMMENT '动态的消息体',
  `properties` text NOT NULL COMMENT '动态的属性',
  `commentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `likeNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被赞的数量',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否私有',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态发布时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `createdTime` (`createdTime`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,1,2,0,'favorite_course','course',2,'','{\"course\":{\"id\":\"2\",\"title\":\"Java \\u0626\\u0627\\u0633\\u0627\\u0633\\u0649\\u064a \\u0628\\u0649\\u0644\\u0649\\u0645 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\",\"picture\":\"\",\"type\":\"normal\",\"rating\":\"5\",\"about\":\"\",\"price\":\"0.00\"}}',0,0,0,1453264421),(2,1,3,0,'favorite_course','course',3,'','{\"course\":{\"id\":\"3\",\"title\":\"\\u0628\\u0649\\u0644\\u0649\\u06af \\u062f\\u06d5\\u0631\\u0633\\u062e\\u0627\\u0646\\u0649\\u0633\\u0649\",\"picture\":\"public:\\/\\/default\\/2016\\/01-20\\/125346a8c8a1625847.jpg\",\"type\":\"normal\",\"rating\":\"0\",\"about\":\"\",\"price\":\"0.00\"}}',0,0,0,1453276497);
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (1,'默认标签',1452852365),(2,'ھەقلىق',1453265705),(3,'ھەقسىز',1453265712);
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '任务标题',
  `description` text COMMENT '任务描述',
  `meta` text COMMENT '任务元信息',
  `userId` int(10) NOT NULL DEFAULT '0',
  `taskType` varchar(100) NOT NULL COMMENT '任务类型',
  `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '批次Id',
  `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '类型id,可以是课时id,作业id等',
  `targetType` varchar(100) DEFAULT NULL COMMENT '类型,可以是课时,作业等',
  `taskStartTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务开始时间',
  `taskEndTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务结束时间',
  `intervalDate` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '历时天数',
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必做任务,0否,1是',
  `completedTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务完成时间',
  `createdTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task`
--

LOCK TABLES `task` WRITE;
/*!40000 ALTER TABLE `task` DISABLE KEYS */;
/*!40000 ALTER TABLE `task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testpaper`
--

DROP TABLE IF EXISTS `testpaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
  `description` text COMMENT '试卷说明',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：\r\n秒)',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
  `target` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状\r\n态：draft,open,closed',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
  `passedScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '通过考试的分数线',
  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `metas` text COMMENT '题型排序',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testpaper`
--

LOCK TABLES `testpaper` WRITE;
/*!40000 ALTER TABLE `testpaper` DISABLE KEYS */;
/*!40000 ALTER TABLE `testpaper` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testpaper_item`
--

DROP TABLE IF EXISTS `testpaper_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testpaper_item`
--

LOCK TABLES `testpaper_item` WRITE;
/*!40000 ALTER TABLE `testpaper_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `testpaper_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testpaper_item_result`
--

DROP TABLE IF EXISTS `testpaper_item_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
  `testId` int(10) unsigned NOT NULL DEFAULT '0',
  `testPaperResultId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
  `score` float(10,1) NOT NULL DEFAULT '0.0',
  `answer` text,
  `teacherSay` text,
  PRIMARY KEY (`id`),
  KEY `testPaperResultId` (`testPaperResultId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testpaper_item_result`
--

LOCK TABLES `testpaper_item_result` WRITE;
/*!40000 ALTER TABLE `testpaper_item_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `testpaper_item_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testpaper_result`
--

DROP TABLE IF EXISTS `testpaper_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `paperName` varchar(255) NOT NULL DEFAULT '',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'testId',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserId',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `teacherSay` text,
  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
  `passedStatus` enum('none','excellent','good','passed','unpassed') DEFAULT NULL,
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
  `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
  `target` varchar(255) NOT NULL DEFAULT '',
  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedTime` int(11) NOT NULL DEFAULT '0',
  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testpaper_result`
--

LOCK TABLES `testpaper_result` WRITE;
/*!40000 ALTER TABLE `testpaper_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `testpaper_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `theme_config`
--

DROP TABLE IF EXISTS `theme_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `theme_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `config` text,
  `confirmConfig` text,
  `allConfig` text,
  `updatedTime` int(11) NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `updatedUserId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `theme_config`
--

LOCK TABLES `theme_config` WRITE;
/*!40000 ALTER TABLE `theme_config` DISABLE KEYS */;
INSERT INTO `theme_config` VALUES (1,'简墨','{\"maincolor\":\"purple\",\"navigationcolor\":\"purple-light\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"background\":\"\",\"categoryId\":\"0\",\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"\\u7f51\\u6821\\u8bfe\\u7a0b\",\"subTitle\":\"\",\"defaultSubTitle\":\"\\u7cbe\\u9009\\u7f51\\u6821\\u8bfe\\u7a0b\\uff0c\\u6ee1\\u8db3\\u4f60\\u7684\\u5b66\\u4e60\\u5174\\u8da3\\u3002\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"\\u8fd1\\u671f\\u76f4\\u64ad\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u5b9e\\u65f6\\u8ddf\\u8e2a\\u76f4\\u64ad\\u8bfe\\u7a0b\\uff0c\\u907f\\u514d\\u8bfe\\u7a0b\\u9057\\u6f0f\\u3002\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"\\u4e2d\\u90e8banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"\\u63a8\\u8350\\u73ed\\u7ea7\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u73ed\\u7ea7\\u5316\\u5b66\\u4e60\\u4f53\\u7cfb\\uff0c\\u7ed9\\u4f60\\u66f4\\u591a\\u7684\\u8bfe\\u7a0b\\u76f8\\u5173\\u670d\\u52a1\\u3002\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"subTitle\":\"\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"checked\",\"select4\":\"checked\",\"background\":\"\",\"code\":\"groups\",\"defaultTitle\":\"\\u52a8\\u6001\",\"defaultSubTitle\":\"\\u53c2\\u4e0e\\u5c0f\\u7ec4\\uff0c\\u7ed3\\u4ea4\\u66f4\\u591a\\u540c\\u5b66\\uff0c\\u5173\\u6ce8\\u8bfe\\u7a0b\\u52a8\\u6001\\u3002\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"\\u63a8\\u8350\\u6559\\u5e08\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u540d\\u5e08\\u6c47\\u96c6\\uff0c\\u4fdd\\u8bc1\\u6559\\u5b66\\u8d28\\u91cf\\u4e0e\\u5b66\\u4e60\\u6548\\u679c\\u3002\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"\"}','{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"background\":\"\",\"categoryId\":\"0\",\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"\\u7f51\\u6821\\u8bfe\\u7a0b\",\"subTitle\":\"\",\"defaultSubTitle\":\"\\u7cbe\\u9009\\u7f51\\u6821\\u8bfe\\u7a0b\\uff0c\\u6ee1\\u8db3\\u4f60\\u7684\\u5b66\\u4e60\\u5174\\u8da3\\u3002\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"\\u8fd1\\u671f\\u76f4\\u64ad\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u5b9e\\u65f6\\u8ddf\\u8e2a\\u76f4\\u64ad\\u8bfe\\u7a0b\\uff0c\\u907f\\u514d\\u8bfe\\u7a0b\\u9057\\u6f0f\\u3002\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"\\u4e2d\\u90e8banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"\\u63a8\\u8350\\u73ed\\u7ea7\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u73ed\\u7ea7\\u5316\\u5b66\\u4e60\\u4f53\\u7cfb\\uff0c\\u7ed9\\u4f60\\u66f4\\u591a\\u7684\\u8bfe\\u7a0b\\u76f8\\u5173\\u670d\\u52a1\\u3002\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"subTitle\":\"\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"checked\",\"select4\":\"checked\",\"background\":\"\",\"code\":\"groups\",\"defaultTitle\":\"\\u52a8\\u6001\",\"defaultSubTitle\":\"\\u53c2\\u4e0e\\u5c0f\\u7ec4\\uff0c\\u7ed3\\u4ea4\\u66f4\\u591a\\u540c\\u5b66\\uff0c\\u5173\\u6ce8\\u8bfe\\u7a0b\\u52a8\\u6001\\u3002\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"\\u63a8\\u8350\\u6559\\u5e08\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u540d\\u5e08\\u6c47\\u96c6\\uff0c\\u4fdd\\u8bc1\\u6559\\u5b66\\u8d28\\u91cf\\u4e0e\\u5b66\\u4e60\\u6548\\u679c\\u3002\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"\"}',NULL,1454577385,1449218369,1),(2,'必利','{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"background\":\"\",\"categoryId\":\"0\",\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"\\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\",\"subTitle\":\"\",\"defaultSubTitle\":\"\\u062a\\u0627\\u0644\\u0644\\u0627\\u0646\\u063a\\u0627\\u0646 \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u0649\\u0631\\u0649\\u060c \\u0642\\u0649\\u0632\\u0632\\u0649\\u0642\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u0646\\u0649 \\u0642\\u0627\\u0646\\u062f\\u0649\\u0631\\u0649\\u062f\\u06c7.\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"\\u8fd1\\u671f\\u76f4\\u64ad\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u5b9e\\u65f6\\u8ddf\\u8e2a\\u76f4\\u64ad\\u8bfe\\u7a0b\\uff0c\\u907f\\u514d\\u8bfe\\u7a0b\\u9057\\u6f0f\\u3002\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"\\u4e2d\\u90e8banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"\\u062a\\u06d5\\u06cb\\u0633\\u0649\\u064a\\u06d5 \\u0633\\u0649\\u0646\\u0649\\u067e\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u73ed\\u7ea7\\u5316\\u5b66\\u4e60\\u4f53\\u7cfb\\uff0c\\u7ed9\\u4f60\\u66f4\\u591a\\u7684\\u8bfe\\u7a0b\\u76f8\\u5173\\u670d\\u52a1\\u3002\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"subTitle\":\"\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"checked\",\"select4\":\"checked\",\"background\":\"\",\"code\":\"groups\",\"defaultTitle\":\"\\u064a\\u06d0\\u06ad\\u0649\\u0644\\u0649\\u0642\\u0644\\u0627\\u0631\",\"defaultSubTitle\":\"\\u06af\\u0648\\u0631\\u06c7\\u067e\\u067e\\u0649\\u0644\\u0627\\u0631\\u063a\\u0627 \\u0642\\u0627\\u062a\\u0646\\u0649\\u0634\\u0649\\u0634\\u060c \\u062a\\u06d0\\u062e\\u0649\\u0645\\u06c7 \\u0643\\u06c6\\u067e \\u0626\\u0648\\u0642\\u06c7\\u063a\\u06c7\\u0686\\u0649\\u0644\\u0627\\u0631 \\u0628\\u0649\\u0644\\u06d5\\u0646 \\u062a\\u0648\\u0646\\u06c7\\u0634\\u06c7\\u0634\\u060c \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u06d5\\u0631\\u0646\\u0649\\u06ad \\u064a\\u06d0\\u06ad\\u0649\\u0644\\u0649\\u0646\\u0649\\u063a\\u0627 \\u062f\\u0649\\u0642\\u0642\\u06d5\\u062a \\u0642\\u0649\\u0644\\u0649\\u0634.\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"\\u63a8\\u8350\\u6559\\u5e08\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u540d\\u5e08\\u6c47\\u96c6\\uff0c\\u4fdd\\u8bc1\\u6559\\u5b66\\u8d28\\u91cf\\u4e0e\\u5b66\\u4e60\\u6548\\u679c\\u3002\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"\"}','{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"background\":\"\",\"categoryId\":\"0\",\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"\\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0649\",\"subTitle\":\"\",\"defaultSubTitle\":\"\\u062a\\u0627\\u0644\\u0644\\u0627\\u0646\\u063a\\u0627\\u0646 \\u062a\\u0648\\u0631 \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u0649\\u0631\\u0649\\u060c \\u0642\\u0649\\u0632\\u0632\\u0649\\u0642\\u0649\\u0634\\u0649\\u06ad\\u0649\\u0632\\u0646\\u0649 \\u0642\\u0627\\u0646\\u062f\\u0649\\u0631\\u0649\\u062f\\u06c7.\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"\\u8fd1\\u671f\\u76f4\\u64ad\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u5b9e\\u65f6\\u8ddf\\u8e2a\\u76f4\\u64ad\\u8bfe\\u7a0b\\uff0c\\u907f\\u514d\\u8bfe\\u7a0b\\u9057\\u6f0f\\u3002\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"\\u4e2d\\u90e8banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"\\u062a\\u06d5\\u06cb\\u0633\\u0649\\u064a\\u06d5 \\u0633\\u0649\\u0646\\u0649\\u067e\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u73ed\\u7ea7\\u5316\\u5b66\\u4e60\\u4f53\\u7cfb\\uff0c\\u7ed9\\u4f60\\u66f4\\u591a\\u7684\\u8bfe\\u7a0b\\u76f8\\u5173\\u670d\\u52a1\\u3002\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"subTitle\":\"\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"checked\",\"select4\":\"checked\",\"background\":\"\",\"code\":\"groups\",\"defaultTitle\":\"\\u064a\\u06d0\\u06ad\\u0649\\u0644\\u0649\\u0642\\u0644\\u0627\\u0631\",\"defaultSubTitle\":\"\\u06af\\u0648\\u0631\\u06c7\\u067e\\u067e\\u0649\\u0644\\u0627\\u0631\\u063a\\u0627 \\u0642\\u0627\\u062a\\u0646\\u0649\\u0634\\u0649\\u0634\\u060c \\u062a\\u06d0\\u062e\\u0649\\u0645\\u06c7 \\u0643\\u06c6\\u067e \\u0626\\u0648\\u0642\\u06c7\\u063a\\u06c7\\u0686\\u0649\\u0644\\u0627\\u0631 \\u0628\\u0649\\u0644\\u06d5\\u0646 \\u062a\\u0648\\u0646\\u06c7\\u0634\\u06c7\\u0634\\u060c \\u062f\\u06d5\\u0631\\u0633\\u0644\\u0649\\u0643\\u0644\\u06d5\\u0631\\u0646\\u0649\\u06ad \\u064a\\u06d0\\u06ad\\u0649\\u0644\\u0649\\u0646\\u0649\\u063a\\u0627 \\u062f\\u0649\\u0642\\u0642\\u06d5\\u062a \\u0642\\u0649\\u0644\\u0649\\u0634.\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"\\u63a8\\u8350\\u6559\\u5e08\",\"subTitle\":\"\",\"background\":\"\",\"defaultSubTitle\":\"\\u540d\\u5e08\\u6c47\\u96c6\\uff0c\\u4fdd\\u8bc1\\u6559\\u5b66\\u8d28\\u91cf\\u4e0e\\u5b66\\u4e60\\u6548\\u679c\\u3002\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"\"}',NULL,1453287872,1453218439,1);
/*!40000 ALTER TABLE `theme_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `targetType` varchar(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型 ID',
  `relationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '从属ID',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `ats` text COMMENT '@(提)到的人',
  `nice` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '加精',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `solved` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有老师回答(已被解决)',
  `lastPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
  `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `location` varchar(1024) DEFAULT NULL COMMENT '地点',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成员人数',
  `maxUsers` int(10) NOT NULL DEFAULT '0' COMMENT '最大人数',
  `actvityPicture` varchar(255) DEFAULT NULL COMMENT '活动图片',
  `status` enum('open','closed') NOT NULL DEFAULT 'open' COMMENT '状态',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题最后一次被编辑或回复时间',
  PRIMARY KEY (`id`),
  KEY `updateTime` (`updateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread`
--

LOCK TABLES `thread` WRITE;
/*!40000 ALTER TABLE `thread` DISABLE KEYS */;
/*!40000 ALTER TABLE `thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread_member`
--

DROP TABLE IF EXISTS `thread_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
  `threadId` int(10) unsigned NOT NULL COMMENT '话题Id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `mobile` varchar(32) DEFAULT NULL COMMENT '手机号码',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='话题成员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread_member`
--

LOCK TABLES `thread_member` WRITE;
/*!40000 ALTER TABLE `thread_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `thread_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread_post`
--

DROP TABLE IF EXISTS `thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题ID',
  `content` text NOT NULL COMMENT '内容',
  `adopted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被采纳(是老师回答)',
  `ats` text COMMENT '@(提)到的人',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `subposts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子话题数量',
  `ups` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `targetType` varchar(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型',
  `targetId` int(10) unsigned NOT NULL COMMENT '所属 类型ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread_post`
--

LOCK TABLES `thread_post` WRITE;
/*!40000 ALTER TABLE `thread_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `thread_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread_vote`
--

DROP TABLE IF EXISTS `thread_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned NOT NULL COMMENT '话题ID',
  `postId` int(10) unsigned NOT NULL COMMENT '回帖ID',
  `action` enum('up','down') NOT NULL COMMENT '投票类型',
  `userId` int(10) unsigned NOT NULL COMMENT '投票人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '投票时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `postId` (`threadId`,`postId`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='话题投票表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread_vote`
--

LOCK TABLES `thread_vote` WRITE;
/*!40000 ALTER TABLE `thread_vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `thread_vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upgrade_logs`
--

DROP TABLE IF EXISTS `upgrade_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upgrade_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteId` int(11) NOT NULL COMMENT 'packageId',
  `installedId` int(11) DEFAULT NULL COMMENT '本地已安装id',
  `ename` varchar(32) NOT NULL COMMENT '名称',
  `cname` varchar(32) NOT NULL COMMENT '中文名称',
  `fromv` varchar(32) DEFAULT NULL COMMENT '初始版本',
  `tov` varchar(32) NOT NULL COMMENT '目标版本',
  `type` smallint(6) NOT NULL COMMENT '升级类型',
  `dbBackPath` text COMMENT '数据库备份文件',
  `srcBackPath` text COMMENT '源文件备份地址',
  `status` varchar(32) NOT NULL COMMENT '状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
  `logtime` int(11) NOT NULL COMMENT '升级时间',
  `uid` int(10) unsigned NOT NULL COMMENT 'uid',
  `ip` varchar(32) DEFAULT NULL COMMENT 'ip',
  `reason` text COMMENT '失败原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='本地升级日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upgrade_logs`
--

LOCK TABLES `upgrade_logs` WRITE;
/*!40000 ALTER TABLE `upgrade_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `upgrade_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upload_files`
--

DROP TABLE IF EXISTS `upload_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
  `targetId` int(11) DEFAULT NULL,
  `targetType` varchar(64) DEFAULT NULL,
  `filename` varchar(1024) NOT NULL DEFAULT '',
  `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
  `size` bigint(20) NOT NULL DEFAULT '0',
  `etag` varchar(256) NOT NULL DEFAULT '',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '长度（音视频则为时长，PPT/文档为页数）',
  `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
  `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
  `convertParams` text COMMENT '文件转换参数',
  `metas` text,
  `metas2` text,
  `type` enum('document','video','audio','image','ppt','other','flash') NOT NULL DEFAULT 'other' COMMENT '文件类型',
  `storage` enum('local','cloud') NOT NULL,
  `isPublic` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开文件',
  `canDownload` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否可下载',
  `usedCount` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
  `updatedTime` int(10) unsigned DEFAULT '0',
  `createdUserId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashId` (`hashId`),
  UNIQUE KEY `convertHash` (`convertHash`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upload_files`
--

LOCK TABLES `upload_files` WRITE;
/*!40000 ALTER TABLE `upload_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `upload_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upload_files_share`
--

DROP TABLE IF EXISTS `upload_files_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files_share` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sourceUserId` int(10) unsigned NOT NULL COMMENT '上传文件的用户ID',
  `targetUserId` int(10) unsigned NOT NULL COMMENT '文件分享目标用户ID',
  `isActive` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `sourceUserId` (`sourceUserId`),
  KEY `targetUserId` (`targetUserId`),
  KEY `createdTime` (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upload_files_share`
--

LOCK TABLES `upload_files_share` WRITE;
/*!40000 ALTER TABLE `upload_files_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `upload_files_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `verifiedMobile` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `payPassword` varchar(64) NOT NULL DEFAULT '',
  `payPasswordSalt` varchar(64) NOT NULL DEFAULT '',
  `uri` varchar(64) NOT NULL DEFAULT '',
  `nickname` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
  `point` int(11) NOT NULL DEFAULT '0',
  `coin` int(11) NOT NULL DEFAULT '0',
  `smallAvatar` varchar(255) NOT NULL DEFAULT '',
  `mediumAvatar` varchar(255) NOT NULL DEFAULT '',
  `largeAvatar` varchar(255) NOT NULL DEFAULT '',
  `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
  `setup` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否初始化设置的，未初始化的可以设置邮箱、昵称。',
  `roles` varchar(255) NOT NULL,
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
  `promotedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `lastPasswordFailTime` int(10) NOT NULL DEFAULT '0',
  `lockDeadline` int(10) NOT NULL DEFAULT '0',
  `consecutivePasswordErrorTimes` int(11) NOT NULL DEFAULT '0',
  `loginTime` int(11) NOT NULL DEFAULT '0',
  `loginIp` varchar(64) NOT NULL DEFAULT '',
  `loginSessionId` varchar(255) NOT NULL DEFAULT '',
  `approvalTime` int(10) unsigned NOT NULL DEFAULT '0',
  `approvalStatus` enum('unapprove','approving','approved','approve_fail') NOT NULL DEFAULT 'unapprove',
  `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0',
  `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdIp` varchar(64) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `inviteCode` varchar(255) DEFAULT NULL COMMENT '邀请码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nickname` (`nickname`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'test@edusoho.com','','avQifS3v1zYa6PYnWiTxDqmUp5U+6uH5jI99MFW0lBE=','76pqmmj93sowkgcgsgoog4cwo088kg0','','','','测试管理员','技术总监','','default',0,0,'public://default/2016/01-28/122705936fd0862024.jpg','public://default/2016/01-28/122705936185051189.jpg','public://default/2016/01-28/122705934fb4341002.jpg',0,1,'|ROLE_USER|ROLE_SUPER_ADMIN|ROLE_TEACHER|',0,0,0,0,0,0,1455418689,'127.0.0.1','a02vjunm97dncbo062v1c6lpq2',0,'unapprove',0,0,'127.0.0.1',1452852362,1455418689,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_approval`
--

DROP TABLE IF EXISTS `user_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_approval` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '用户ID',
  `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号',
  `faceImg` varchar(500) NOT NULL DEFAULT '',
  `backImg` varchar(500) NOT NULL DEFAULT '',
  `truename` varchar(255) DEFAULT NULL COMMENT '名称',
  `note` text COMMENT '认证信息',
  `status` enum('unapprove','approving','approved','approve_fail') NOT NULL COMMENT '是否通过：1是 0否',
  `operatorId` int(10) unsigned DEFAULT NULL COMMENT '审核人',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户认证表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_approval`
--

LOCK TABLES `user_approval` WRITE;
/*!40000 ALTER TABLE `user_approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_bind`
--

DROP TABLE IF EXISTS `user_bind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `fromId` varchar(32) NOT NULL,
  `toId` int(10) unsigned NOT NULL COMMENT '绑定的用户ID',
  `token` varchar(255) NOT NULL DEFAULT '',
  `refreshToken` varchar(255) NOT NULL DEFAULT '',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`fromId`),
  UNIQUE KEY `type_2` (`type`,`toId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_bind`
--

LOCK TABLES `user_bind` WRITE;
/*!40000 ALTER TABLE `user_bind` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_bind` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_field`
--

DROP TABLE IF EXISTS `user_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldName` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `seq` int(10) unsigned NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(100) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_field`
--

LOCK TABLES `user_field` WRITE;
/*!40000 ALTER TABLE `user_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_fortune_log`
--

DROP TABLE IF EXISTS `user_fortune_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_fortune_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `number` int(10) NOT NULL,
  `action` varchar(20) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_fortune_log`
--

LOCK TABLES `user_fortune_log` WRITE;
/*!40000 ALTER TABLE `user_fortune_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_fortune_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_pay_agreement`
--

DROP TABLE IF EXISTS `user_pay_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_pay_agreement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
  `bankName` varchar(255) NOT NULL COMMENT '银行名称',
  `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
  `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
  `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
  `bankId` int(8) NOT NULL COMMENT '对应的银行Id',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_pay_agreement`
--

LOCK TABLES `user_pay_agreement` WRITE;
/*!40000 ALTER TABLE `user_pay_agreement` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_pay_agreement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profile`
--

DROP TABLE IF EXISTS `user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profile` (
  `id` int(10) unsigned NOT NULL,
  `truename` varchar(255) NOT NULL DEFAULT '',
  `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret',
  `iam` varchar(255) NOT NULL DEFAULT '' COMMENT 'user''s type',
  `birthday` date DEFAULT NULL,
  `city` varchar(64) NOT NULL DEFAULT '',
  `mobile` varchar(32) NOT NULL DEFAULT '',
  `qq` varchar(32) NOT NULL DEFAULT '',
  `signature` text,
  `about` text,
  `company` varchar(255) NOT NULL DEFAULT '',
  `job` varchar(255) NOT NULL DEFAULT '',
  `school` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(255) NOT NULL DEFAULT '',
  `weibo` varchar(255) NOT NULL DEFAULT '',
  `weixin` varchar(255) NOT NULL DEFAULT '',
  `site` varchar(255) NOT NULL DEFAULT '',
  `intField1` int(11) DEFAULT NULL,
  `intField2` int(11) DEFAULT NULL,
  `intField3` int(11) DEFAULT NULL,
  `intField4` int(11) DEFAULT NULL,
  `intField5` int(11) DEFAULT NULL,
  `dateField1` date DEFAULT NULL,
  `dateField2` date DEFAULT NULL,
  `dateField3` date DEFAULT NULL,
  `dateField4` date DEFAULT NULL,
  `dateField5` date DEFAULT NULL,
  `floatField1` float(10,2) DEFAULT NULL,
  `floatField2` float(10,2) DEFAULT NULL,
  `floatField3` float(10,2) DEFAULT NULL,
  `floatField4` float(10,2) DEFAULT NULL,
  `floatField5` float(10,2) DEFAULT NULL,
  `varcharField1` varchar(1024) DEFAULT NULL,
  `varcharField2` varchar(1024) DEFAULT NULL,
  `varcharField3` varchar(1024) DEFAULT NULL,
  `varcharField4` varchar(1024) DEFAULT NULL,
  `varcharField5` varchar(1024) DEFAULT NULL,
  `varcharField6` varchar(1024) DEFAULT NULL,
  `varcharField7` varchar(1024) DEFAULT NULL,
  `varcharField8` varchar(1024) DEFAULT NULL,
  `varcharField9` varchar(1024) DEFAULT NULL,
  `varcharField10` varchar(1024) DEFAULT NULL,
  `textField1` text,
  `textField2` text,
  `textField3` text,
  `textField4` text,
  `textField5` text,
  `textField6` text,
  `textField7` text,
  `textField8` text,
  `textField9` text,
  `textField10` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profile`
--

LOCK TABLES `user_profile` WRITE;
/*!40000 ALTER TABLE `user_profile` DISABLE KEYS */;
INSERT INTO `user_profile` VALUES (1,'艾合麦提江','652825198809180019','male','',NULL,'','13394912499','515082609','Torghay','<p>大速度速度萨达的，大大是大大，是打算打算的。撒大实打实说的大叔大叔的，打算打算大。阿三打算打算大，啊撒大实打实。</p>\n','乌鲁木齐必利网络科技有限责任公司','程序员','','','http://biligcc.dev','Torghay_Bilig','http://biligcc.dev',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','','','','','','','','','','','','','','');
/*!40000 ALTER TABLE `user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_secure_question`
--

DROP TABLE IF EXISTS `user_secure_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_secure_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `securityQuestionCode` varchar(64) NOT NULL DEFAULT '',
  `securityAnswer` varchar(64) NOT NULL DEFAULT '',
  `securityAnswerSalt` varchar(64) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_secure_question`
--

LOCK TABLES `user_secure_question` WRITE;
/*!40000 ALTER TABLE `user_secure_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_secure_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN的校验次数限制(0表示不限制)',
  `remainedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKE剩余校验次数',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`(6))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
INSERT INTO `user_token` VALUES (1,'31mgpxwhvj6s4gg8scow8wkskgos0cg',1,'mobile_login','s:2:\"N;\";',0,0,1455868378,1453276378);
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip`
--

DROP TABLE IF EXISTS `vip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vip` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `levelId` int(10) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  `boughtType` enum('new','upgrade','renew','edit') NOT NULL COMMENT '购买类型',
  `boughtTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买时间',
  `boughtDuration` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买时长',
  `boughtUnit` enum('month','year') NOT NULL COMMENT '开通方式按月、按年',
  `boughtAmount` float(10,2) NOT NULL DEFAULT '0.00',
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买会员的订单ID',
  `operatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员操作时为管理员用户ID',
  `createdTime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip`
--

LOCK TABLES `vip` WRITE;
/*!40000 ALTER TABLE `vip` DISABLE KEYS */;
/*!40000 ALTER TABLE `vip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip_history`
--

DROP TABLE IF EXISTS `vip_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vip_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '购买用户',
  `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员类型',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0',
  `boughtType` enum('new','upgrade','renew','edit','cancel') NOT NULL COMMENT '购买类型',
  `boughtTime` int(10) unsigned NOT NULL DEFAULT '0',
  `boughtDuration` int(10) unsigned NOT NULL DEFAULT '0',
  `boughtUnit` enum('none','month','year') NOT NULL DEFAULT 'none',
  `boughtAmount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '购买金额',
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买会员的订单ID',
  `operatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员操作时为管理员用户ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip_history`
--

LOCK TABLES `vip_history` WRITE;
/*!40000 ALTER TABLE `vip_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `vip_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vip_level`
--

DROP TABLE IF EXISTS `vip_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vip_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '会员类型名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '示意图标',
  `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '展示图片',
  `monthPrice` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '月费价格',
  `yearPrice` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '年费价格',
  `description` text COMMENT '一句话描述',
  `freeLearned` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费学习课程',
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `createdTime` int(10) unsigned NOT NULL,
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员类型表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vip_level`
--

LOCK TABLES `vip_level` WRITE;
/*!40000 ALTER TABLE `vip_level` DISABLE KEYS */;
/*!40000 ALTER TABLE `vip_level` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-02-14 10:34:49

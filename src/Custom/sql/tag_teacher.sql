/*
 Navicat Premium Data Transfer

 Source Server         : Docker
 Source Server Type    : MySQL
 Source Server Version : 50541
 Source Host           : localhost
 Source Database       : edusoho-dev

 Target Server Type    : MySQL
 Target Server Version : 50541
 File Encoding         : utf-8

 Date: 04/02/2015 13:46:53 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `tag_teacher`
-- ----------------------------
DROP TABLE IF EXISTS `tag_teacher`;
CREATE TABLE `tag_teacher` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;

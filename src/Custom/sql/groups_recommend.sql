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

 Date: 04/02/2015 13:42:59 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `groups_recommend`
-- ----------------------------
DROP TABLE IF EXISTS `groups_recommend`;
CREATE TABLE `groups_recommend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `groupID` int(10) NOT NULL,
  `seq` int(10) NOT NULL,
  `createdTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;

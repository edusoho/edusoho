<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130524144924 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("
			CREATE TABLE `cache` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `data` longblob,
			  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
			  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `name` (`name`),
			  KEY `expiredTime` (`expiredTime`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `category` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `path` varchar(255) NOT NULL,
			  `weight` int(11) NOT NULL DEFAULT '0',
			  `groupId` int(10) unsigned NOT NULL,
			  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `uri` (`code`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `category_group` (
			  `id` int(11) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `depth` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

			CREATE TABLE `course` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(1024) NOT NULL,
			  `type` enum('online','offline') NOT NULL,
			  `state` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `price` float(10,2) NOT NULL DEFAULT '0.00',
			  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
			  `tags` text NOT NULL,
			  `smallPicture` varchar(255) NOT NULL,
			  `middlePicture` varchar(255) NOT NULL,
			  `largePicture` varchar(255) NOT NULL,
			  `about` text NOT NULL,
			  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
			  `endTime` int(10) unsigned NOT NULL DEFAULT '0',
			  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
			  `address` varchar(255) NOT NULL,
			  `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
			  `userId` int(10) unsigned NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `course_material` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` int(10) unsigned NOT NULL,
			  `lessonId` int(10) unsigned NOT NULL,
			  `title` varchar(1024) NOT NULL,
			  `description` text NOT NULL,
			  `fileUri` varchar(255) NOT NULL,
			  `fileMime` varchar(255) NOT NULL,
			  `fileSize` int(10) unsigned NOT NULL,
			  `userId` int(10) unsigned NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			CREATE TABLE `course_member` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` int(10) unsigned NOT NULL,
			  `userId` int(10) unsigned NOT NULL,
			  `role` enum('admin','member') NOT NULL DEFAULT 'member' COMMENT '0:待验证,1:成员,:2:创建人',
			  `truename` varchar(255) NOT NULL,
			  `email` varchar(255) NOT NULL,
			  `mobile` varchar(255) NOT NULL,
			  `company` varchar(255) NOT NULL,
			  `job` varchar(255) NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `course_thread` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` varchar(32) NOT NULL,
			  `lessonId` int(11) NOT NULL,
			  `userId` int(10) unsigned NOT NULL DEFAULT '0',
			  `type` enum('discussion','question') NOT NULL DEFAULT 'discussion',
			  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0',
			  `isClosed` int(10) unsigned NOT NULL DEFAULT '0',
			  `title` varchar(255) NOT NULL,
			  `content` text NOT NULL,
			  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
			  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击查看的次数',
			  `followNum` int(11) NOT NULL,
			  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0',
			  `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0',
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `course_thread_post` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` varchar(32) NOT NULL,
			  `lessonId` int(10) unsigned NOT NULL,
			  `threadId` int(10) unsigned NOT NULL,
			  `userId` int(10) unsigned NOT NULL,
			  `content` text NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			CREATE TABLE `file_group` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `code` varchar(255) NOT NULL,
			  `public` tinyint(4) NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `location` (
			  `id` bigint(20) unsigned NOT NULL,
			  `parentId` bigint(20) NOT NULL DEFAULT '0',
			  `name` varchar(255) NOT NULL,
			  `pinyin` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			CREATE TABLE `message` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
			  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
			  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
			  `content` text NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `message_conversation` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
			  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
			  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
			  `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
			  `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后一条信息，用Json显示',
			  `latestMessageTime` int(10) unsigned NOT NULL,
			  `latestMessageContent` text NOT NULL,
			  `unreadNum` int(10) unsigned NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `message_relation` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对话id',
			  `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息Id',
			  `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0表示未读',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `session` (
			  `session_id` varchar(255) NOT NULL,
			  `session_value` text NOT NULL,
			  `session_time` int(11) NOT NULL,
			  PRIMARY KEY (`session_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			CREATE TABLE `setting` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `value` longblob,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `tag` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `user` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `email` varchar(128) NOT NULL,
			  `password` varchar(64) NOT NULL,
			  `salt` varchar(32) NOT NULL,
			  `uri` varchar(64) NOT NULL,
			  `nickname` varchar(64) NOT NULL,
			  `tags` varchar(255) NOT NULL,
			  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
			  `point` int(11) NOT NULL DEFAULT '0',
			  `coin` int(11) NOT NULL DEFAULT '0',
			  `smallAvatar` varchar(255) NOT NULL,
			  `mediumAvatar` text NOT NULL,
			  `largeAvatar` varchar(255) NOT NULL,
			  `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
			  `roles` varchar(255) NOT NULL,
			  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `loginTime` int(11) NOT NULL DEFAULT '0',
			  `loginIp` varchar(64) NOT NULL,
			  `createdIp` varchar(64) NOT NULL,
			  `createdTime` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			CREATE TABLE `user_bind` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `type` varchar(64) NOT NULL,
			  `fromId` varchar(32) NOT NULL,
			  `toId` int(10) unsigned NOT NULL COMMENT '绑定的用户ID',
			  `token` varchar(64) NOT NULL,
			  `refreshToken` varchar(255) NOT NULL,
			  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `type` (`type`,`fromId`),
			  UNIQUE KEY `type_2` (`type`,`toId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			CREATE TABLE `user_profile` (
			  `id` int(10) unsigned NOT NULL,
			  `truename` varchar(255) NOT NULL,
			  `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret',
			  `birthday` date DEFAULT NULL,
			  `city` varchar(64) NOT NULL,
			  `mobile` varchar(32) NOT NULL,
			  `qq` varchar(32) NOT NULL,
			  `signature` text NOT NULL,
			  `about` text NOT NULL,
			  `company` varchar(255) NOT NULL,
			  `job` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

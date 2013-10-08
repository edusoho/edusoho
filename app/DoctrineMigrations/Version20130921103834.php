<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130921103834 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
		CREATE TABLE IF NOT EXISTS `activity` (
			  `explain` varchar(60) CHARACTER SET utf8 DEFAULT NULL COMMENT '活动说明',
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `title` varchar(1024) CHARACTER SET utf8 NOT NULL COMMENT '主标题',
			  `subtitle` varchar(1024) CHARACTER SET utf8 DEFAULT NULL COMMENT '子标题',
			  `status` enum('draft','published','closed') CHARACTER SET utf8 NOT NULL DEFAULT 'draft' COMMENT '状态',
			  `price` varchar(1024) CHARACTER SET utf8 NOT NULL DEFAULT '0.00' COMMENT '价格',
			  `rating` float NOT NULL DEFAULT '0' COMMENT '评价',
			  `ratingnum` float NOT NULL DEFAULT '0' COMMENT '评论数',
			  `categoryid` int(10) NOT NULL DEFAULT '0' COMMENT '分类表ID',
			  `tagsid` text CHARACTER SET utf8 NOT NULL COMMENT '标签表ID',
			  `smallPicture` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '小图',
			  `middlePicture` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '中图',
			  `largePiceture` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '大图',
			  `about` text CHARACTER SET utf8 NOT NULL COMMENT '相关表述',
			  `experterid` text CHARACTER SET utf8 COMMENT '牛人ID',
			  `startTime` int(10) DEFAULT '0' COMMENT '开始时间',
			  `endTime` int(10) DEFAULT '0' COMMENT '结束时间',
			  `locationId` int(10) NOT NULL DEFAULT '0' COMMENT '地点ID',
			  `address` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '地址',
			  `studentNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '参与学生人数',
			  `userId` int(11) unsigned NOT NULL COMMENT '添加人ID',
			  `form` varchar(1024) CHARACTER SET utf8 DEFAULT NULL COMMENT '活动形式',
			  `courseId` text CHARACTER SET utf8 COMMENT '课程ID',
			  `photoid` text CHARACTER SET utf8 COMMENT '专辑',
			  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='活动表' AUTO_INCREMENT=0 ;
        ");
		 $this->addSql("
		 	CREATE TABLE IF NOT EXISTS `activity_material` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `activityId` int(10) unsigned NOT NULL,
				  `title` varchar(1024) NOT NULL,
				  `description` text NOT NULL,
				  `fileUri` varchar(255) NOT NULL,
				  `fileMime` varchar(255) NOT NULL,
				  `fileSize` int(10) unsigned NOT NULL,
				  `userId` int(10) unsigned NOT NULL,
				  `createdTime` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
		 ");
		 $this->addSql("
		 	CREATE TABLE IF NOT EXISTS `activity_member` (
			  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `activityId` int(10) NOT NULL COMMENT '活动ID',
			  `userId` int(10) NOT NULL COMMENT '用户ID',
			  `createdTime` int(10) NOT NULL COMMENT '创建时间',
			  `mobile` varchar(25) CHARACTER SET utf8 DEFAULT NULL COMMENT '电话',
			  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '职位',
			  `job` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '公司',
			  `aboutinfo` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='活动报名表' AUTO_INCREMENT=0 ;
		 ");
		 $this->addSql("
		CREATE TABLE IF NOT EXISTS `activity_thread` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `activityId` int(10) NOT NULL COMMENT '活动ID',
			  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
			  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
			  `isElite` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否精华',
			  `isClosed` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭',
			  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '标题',
			  `content` text CHARACTER SET utf8 NOT NULL COMMENT '内容',
			  `postnum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回答数量',
			  `followNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
			  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人',
			  `latesPostTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
			  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='活动问题表' AUTO_INCREMENT=0 ;
		 ");
		 $this->addSql("
		 	CREATE TABLE IF NOT EXISTS `activity_thread_post` (
			  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `parentid` int(10) DEFAULT NULL COMMENT '父ID',
			  `activityId` int(10) NOT NULL COMMENT '公开课ID',
			  `threadid` int(10) NOT NULL COMMENT '问题ID',
			  `userid` int(10) NOT NULL COMMENT '用户ID',
			  `content` text CHARACTER SET utf8 NOT NULL COMMENT '回复内容',
			  `createdTime` int(11) NOT NULL COMMENT '创建时间',
			  PRIMARY KEY (`id`),
			  KEY `userid` (`userid`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='活动问题回复表' AUTO_INCREMENT=0 ;
		 ");
		 $this->addSql("
		 	CREATE TABLE IF NOT EXISTS `photo_comment` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
			  `imgId` int(10) NOT NULL COMMENT '活动ID',
			  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
			  `content` text CHARACTER SET utf8 NOT NULL COMMENT '内容',
			  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='图片评论表' AUTO_INCREMENT=0 ;
		 ");
		 $this->addSql("
		 	CREATE TABLE IF NOT EXISTS `photo_file` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
			  `userId` int(10) unsigned NOT NULL DEFAULT '0',
			  `url` varchar(255) NOT NULL,
			  `title` varchar(225) NOT NULL COMMENT '图片标题',
			  `content` text COMMENT '图片描述',
			  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;
		 ");
		 $this->addSql("
		 		CREATE TABLE IF NOT EXISTS `photo_group` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
				  `name` varchar(1024) CHARACTER SET utf8 NOT NULL COMMENT '名称',
				  `tagIds` text NOT NULL COMMENT '标签表ID',
				  `userId` int(11) unsigned NOT NULL COMMENT '添加人ID',
				  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='活动表' AUTO_INCREMENT=11 ;
		 ");


		
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs

    }
}

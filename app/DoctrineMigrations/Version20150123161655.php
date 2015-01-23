<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150123161655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL COMMENT '标题',
              `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
              `about` text COMMENT '简介',
              `courseInstruction` text COMMENT '课程说明',
              `price` float(10,2) unsigned NOT NULL DEFAULT '0.00',
              `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
              `smallPicture` varchar(255) NOT NULL DEFAULT '',
              `middlePicture` varchar(255) NOT NULL DEFAULT '',
              `largePicture` varchar(255) NOT NULL DEFAULT '',
              `teacherId` int(10) unsigned NOT NULL DEFAULT '0',
              `courseIds` varchar(255) NOT NULL DEFAULT '',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
              `auditorNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '旁听生数',
              `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `classroom_review` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `classId` int(10) unsigned NOT NULL DEFAULT '0',
              `title` varchar(255) NOT NULL DEFAULT '',
              `content` text NOT NULL,
              `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分0-5',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("
           CREATE TABLE IF NOT EXISTS `thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `targetaType` varchar(255) NOT NULL DEFAULT 'class_thread' COMMENT '所属 类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0',
              `title` varchar(255) NOT NULL,
              `content` text,
              `isElite` int(10) unsigned NOT NULL DEFAULT '0',
              `isStick` int(10) unsigned NOT NULL DEFAULT '0',
              `lastPostMemberId` int(10) unsigned NOT NULL DEFAULT '0',
              `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0',
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
              `status` enum('open','closed') NOT NULL DEFAULT 'open',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("
           CREATE TABLE IF NOT EXISTS `thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL DEFAULT '0',
              `content` text NOT NULL,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `postId` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

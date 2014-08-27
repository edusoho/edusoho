<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140826213649 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE `class_thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题ID',
              `classId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题所属课程ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题发布人ID',
              `type` enum('discussion','question') NOT NULL DEFAULT 'discussion' COMMENT '话题类型',
              `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
              `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否精华',
              `isClosed` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭',
              `title` varchar(255) NOT NULL COMMENT '话题标题',
              `content` text COMMENT '话题内容',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
              `followNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
              `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
              `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");

        $this->addSql("
            CREATE TABLE `class_thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题回复ID',
              `classId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属课程ID',
              `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属话题ID',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复人',
              `isElite` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否精华',
              `content` text NOT NULL COMMENT '正文',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

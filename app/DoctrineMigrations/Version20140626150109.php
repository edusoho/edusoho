<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140626150109 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE `groups` (
              `id` int(10) unsigned NOT NULL COMMENT '小组id',
              `title` varchar(100) NOT NULL COMMENT '小组名称',
              `about` text COMMENT '小组介绍',
              `logo` varchar(100) NOT NULL DEFAULT '' COMMENT 'logo',
              `backgroundLogo` varchar(100) NOT NULL DEFAULT '',
              `enum` varchar(20) NOT NULL DEFAULT 'open',
              `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `ownerId` int(10) unsigned NOT NULL COMMENT '小组组长id',
              `createdTime` int(11) unsigned NOT NULL COMMENT '创建小组时间'
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE `groups_member` (
              `id` int(10) unsigned NOT NULL COMMENT '成员id主键',
              `groupId` int(10) unsigned NOT NULL COMMENT '小组id',
              `userId` int(10) unsigned NOT NULL COMMENT '用户id',
              `role` varchar(100) NOT NULL DEFAULT 'member',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(11) unsigned NOT NULL COMMENT '加入时间'
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE `groups_thread` (
              `id` int(10) unsigned NOT NULL COMMENT '话题id',
              `title` varchar(1024) NOT NULL COMMENT '话题标题',
              `content` text COMMENT '话题内容',
              `isElite` int(11) unsigned NOT NULL DEFAULT '0',
              `isStick` int(11) unsigned NOT NULL DEFAULT '0',
              `lastPostMemberId` int(10) unsigned NOT NULL,
              `lastPostTime` int(10) unsigned NOT NULL,
              `groupId` int(10) unsigned NOT NULL,
              `userId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL COMMENT '添加时间',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0',
              `enum` varchar(20) NOT NULL DEFAULT 'open'
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            CREATE TABLE `groups_thread_post` (
              `id` int(10) unsigned NOT NULL COMMENT 'id主键',
              `threadId` int(11) unsigned NOT NULL COMMENT '话题id',
              `content` text NOT NULL COMMENT '回复内容',
              `userId` int(10) unsigned NOT NULL COMMENT '回复人id',
              `createdTime` int(10) unsigned NOT NULL COMMENT '回复时间'
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

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
           CREATE TABLE IF NOT EXISTS `thread` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `targetaType` varchar(255) NOT NULL DEFAULT 'classroom_thread' COMMENT '所属 类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型 ID',
              `title` varchar(255) NOT NULL COMMENT '标题',
              `content` text COMMENT '内容',
              `isElite` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加精',
              `isStick` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
              `lastPostMemberId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
              `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
              `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
              `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
              `status` enum('open','closed') NOT NULL DEFAULT 'open' COMMENT '状态',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题最后一次被编辑或回复时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("
           CREATE TABLE IF NOT EXISTS `thread_post` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题ID',
              `content` text NOT NULL COMMENT '内容',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `postId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

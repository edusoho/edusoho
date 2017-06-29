<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150226141602 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE `thread_vote` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `threadId` int(10) unsigned NOT NULL COMMENT '话题ID',
              `postId` int(10) unsigned NOT NULL COMMENT '回帖ID',
              `action` enum('up','down') NOT NULL COMMENT '投票类型',
              `userId` int(10) unsigned NOT NULL COMMENT '投票人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '投票时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `postId` (`threadId`,`postId`,`userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='话题投票表';
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

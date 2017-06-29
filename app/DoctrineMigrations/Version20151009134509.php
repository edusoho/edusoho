<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151009134509 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE `batch_notification` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '群发通知id',
                `type` enum('text', 'image', 'video', 'audio')  NOT NULL DEFAULT 'text' COMMENT '通知类型' ,
                `title` text NOT NULL COMMENT '通知标题',
                `fromId` int(10) unsigned NOT NULL COMMENT '发送人id',
                `content` text NOT NULL COMMENT '通知内容',
                `targetType` text NOT NULL COMMENT '通知发送对象group,global,course,classroom等',
                `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知发送对象ID',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送通知时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='群发通知表';
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

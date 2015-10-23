<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150408093736 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `thread` ADD `startTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始时间' AFTER `status`;");
        $this->addSql("ALTER TABLE `thread` ADD `endTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间' AFTER `startTIme`;");
        $this->addSql("ALTER TABLE `thread` ADD `maxUsers` INT(10) NOT NULL DEFAULT '0' COMMENT '最大人数' AFTER `hitNum`;");
        $this->addSql("ALTER TABLE `thread` ADD `location` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地点' AFTER `lastPostTime`;");
        $this->addSql("ALTER TABLE `thread` ADD `memberNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '成员人数' AFTER `hitNum`;");
        
        $this->addSql("CREATE TABLE `thread_member` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
            `threadId` int(10) unsigned NOT NULL COMMENT '话题Id',
            `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
            `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
            `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
            `mobile` varchar(32) DEFAULT NULL COMMENT '手机号码',
            `createdTIme` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='话题成员表';
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

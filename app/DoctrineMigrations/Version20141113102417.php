<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141113102417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `tag_group` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `type` varchar(32) NOT NULL COMMENT '类型',
              `name` varchar(128) NOT NULL COMMENT '名称',
              `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否无效',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='标签组' AUTO_INCREMENT=2 ;
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `tag2` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `groupId` int(10) unsigned NOT NULL COMMENT '标签id',
              `name` varchar(128) NOT NULL COMMENT '值',
              `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否无效',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`),
              KEY `groupId` (`groupId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

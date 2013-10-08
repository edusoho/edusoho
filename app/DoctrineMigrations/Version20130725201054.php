<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130725201054 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP TABLE IF EXISTS `navigation`;
            CREATE TABLE IF NOT EXISTS `navigation` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(255) NOT NULL COMMENT '文案',
              `url` varchar(300) NOT NULL COMMENT 'URL',
              `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序,数字替代',
              `createdTime` int(11) NOT NULL,
              `updateTime` int(11) NOT NULL,
              `type` enum('top','foot') NOT NULL COMMENT '类型',
              `status` enum('close','open') NOT NULL COMMENT '状态，开启或者关闭',
              `openNewWindow` enum('yes','no') NOT NULL COMMENT '是否新开窗口',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='导航数据表' AUTO_INCREMENT=1 ;
            ");
    }

    public function down(Schema $schema)
    {

    }
}

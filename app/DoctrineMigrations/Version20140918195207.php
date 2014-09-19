<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140918195207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("CREATE TABLE `credit_log` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`userId` int(10) NOT NULL DEFAULT '0' COMMENT 'userId',
			`type` enum('point','coin') NOT NULL DEFAULT 'point' COMMENT '积分类型',
			`number` int(10) NOT NULL DEFAULT '0' COMMENT '积分数值',
			`action` varchar(255) NOT NULL DEFAULT '' COMMENT '操作类型',
			`description` varchar(255) NOT NULL DEFAULT '' COMMENT '积分说明',
			`createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

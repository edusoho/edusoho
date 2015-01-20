<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150119111017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("CREATE TABLE `user_invoice` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
			`userId` int(10) unsigned NOT NULL COMMENT '用户Id',
			`title` varchar(255) NOT NULL COMMENT '发票抬头',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户发票表';
    	");
        $this->addSql("ALTER TABLE `user_invoice` ADD `createdTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `title`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

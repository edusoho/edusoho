<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130626000125 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
			CREATE TABLE `course_order` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `sn` varchar(32) CHARACTER SET utf8 NOT NULL,
			  `status` enum('created','paid') CHARACTER SET utf8 NOT NULL,
			  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
			  `courseId` int(10) unsigned NOT NULL,
			  `price` float unsigned NOT NULL,
			  `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `giftTo` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
			  `userId` int(10) unsigned NOT NULL,
			  `payment` enum('alipay','tenpay') CHARACTER SET utf8 NOT NULL,
			  `bank` varchar(32) CHARACTER SET utf8 DEFAULT NULL COMMENT '银行编号',
			  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `sn` (`sn`)
			) ENGINE=InnoDB;
		");

		$this->addSql("
			CREATE TABLE `course_order_log` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `orderId` int(10) unsigned NOT NULL,
			  `type` varchar(32) NOT NULL,
			  `message` text,
			  `data` text,
			  `userId` int(10) unsigned NOT NULL,
			  `ip` varchar(255) NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `orderId` (`orderId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141119145748 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    	$this->addSql("
            CREATE TABLE IF NOT EXISTS `cash_orders_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `orderId` int(10) unsigned NOT NULL,
            `message` text,
            `data` text,
            `userId` int(10) unsigned NOT NULL DEFAULT '0',
            `ip` varchar(255) NOT NULL,
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            `type` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
        $this->addSql("
          CREATE TABLE IF NOT EXISTS `cash_orders` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `sn` varchar(32) NOT NULL COMMENT '订单号',
            `status` enum('created','paid','cancelled') NOT NULL,
            `title` varchar(255) NOT NULL,
            `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00',
            `payment` enum('none','alipay') NOT NULL DEFAULT 'none',
            `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
            `note` varchar(255) NOT NULL DEFAULT '',
            `userId` int(10) unsigned NOT NULL DEFAULT '0',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
        $this->addSql("
              CREATE TABLE IF NOT EXISTS `cash_account` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL,
                `cash` float(10,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id`),
                UNIQUE KEY `userId` (`userId`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `cash_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '帐号ID，即用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `orderSn` varchar(40) NOT NULL COMMENT '订单号',
              `category` varchar(128) NOT NULL DEFAULT '' COMMENT '帐目类目',
              `note` text COMMENT '备注',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tradeNo` (`sn`),
              UNIQUE KEY `orderSn` (`orderSn`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='帐目流水' AUTO_INCREMENT=1 ;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

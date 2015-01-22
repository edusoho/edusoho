<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150122095147 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("CREATE TABLE `order_invoice` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
			`orderId` int(10) unsigned NOT NULL COMMENT '订单Id',
			`userId` int(10) unsigned NOT NULL COMMENT '用户Id',
			`title` text NOT NULL COMMENT '发票抬头',
			`type` varchar(255) NOT NULL COMMENT '发票类别',
			`comment` text COMMENT '备注',
			`amount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '发票金额',
			`createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单发票表';
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

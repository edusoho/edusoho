<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150120150858 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("CREATE TABLE `shipping_address` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
			`userId` int(10) unsigned NOT NULL COMMENT '用户Id',
			`contactName` varchar(255) NOT NULL COMMENT '收货人姓名',
			`region` text NOT NULL COMMENT '地区',
			`address` text NOT NULL COMMENT '详细地址',
			`postCode` int(10) unsigned NOT NULL COMMENT '邮编',
			`mobileNo` int(15) unsigned DEFAULT NULL COMMENT '手机号码',
			`telNo` varchar(255) DEFAULT NULL COMMENT '电话',
			`isDefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认地址',
			`createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户收货地址表';
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

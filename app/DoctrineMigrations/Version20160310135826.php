<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160310135826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `dictionary_item` (
                     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                     `type` varchar(255) NOT NULL COMMENT '字典类型',
                     `code` varchar(64) DEFAULT NULL COMMENT '编码',
                     `name` varchar(255) NOT NULL COMMENT '字典内容名称',
                     `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
                     `createdTime` int(10) unsigned NOT NULL,
                     `updateTime` int(10) unsigned DEFAULT '0',
                     PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8
                    ");

        $this->addSql("INSERT INTO `dictionary_item` (`id`, `type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('1', 'refund_reason', NULL, '课程内容质量差', '0', '0', '0');");

        $this->addSql("INSERT INTO `dictionary_item` (`id`, `type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('2', 'refund_reason', NULL, '老师服务态度不好', '0', '0', '0');");

        $this->addSql("CREATE TABLE `dictionary` (
                     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                     `name` varchar(255) NOT NULL COMMENT '字典名称',
                     `type` varchar(255) NOT NULL COMMENT '字典类型',
                     PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8");

        $this->addSql("INSERT INTO `dictionary` (`id`, `name`, `type`) VALUES ('1', '退学原因', 'refund_reason');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

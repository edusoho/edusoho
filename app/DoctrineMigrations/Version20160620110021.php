<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160620110021 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            DROP TABLE IF EXISTS `referer_log`;
            CREATE TABLE `referer_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `targetId` int(11) NOT NULL COMMENT '模块ID',
              `targetType` varchar(64) NOT NULL COMMENT '模块类型',
              `targetInnerType` VARCHAR(64) NULL COMMENT '模块自身的类型',
              `sourceUrl`  varchar(255) DEFAULT '' COMMENT '访问来源Url',
              `sourceHost` varchar(80)  DEFAULT '' COMMENT '访问来源HOST',
              `sourceName` varchar(64)  DEFAULT '' COMMENT '访问来源站点名称',
              `orderCount` int(10) unsigned  DEFAULT '0'  COMMENT '促成订单数',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问时间',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问者',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='模块(课程|班级|公开课|...)的访问来源日志';

            DROP TABLE IF EXISTS `order_referer_log`;
            CREATE TABLE `order_referer_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `refererLogId` int(11) NOT NULL COMMENT '促成订单的访问日志ID',
              `orderId` int(10) unsigned  DEFAULT '0'  COMMENT '订单ID',
              `sourceTargetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
              `sourceTargetType` varchar(64) NOT NULL DEFAULT '' COMMENT '来源类型',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单的对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的对象ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '订单支付时间',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '订单支付者',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单促成日志';
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

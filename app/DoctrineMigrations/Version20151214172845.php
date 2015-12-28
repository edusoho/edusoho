<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214172845 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isTableExist('coupon')) {
            $this->addSql("
            CREATE TABLE `coupon` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `code` varchar(255) NOT NULL COMMENT '优惠码',
              `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
              `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态',
              `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
              `batchId` int(10) unsigned  NULL DEFAULT NULL COMMENT '批次号',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
              `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
              `targetType` varchar(64) NUll DEFAULT NULL COMMENT '使用对象类型',
              `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象',
              `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
              `orderTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
              `createdTime` int(10) unsigned NOT NULL,
              `receiveTime` INT(10) unsigned NULL DEFAULT '0'  COMMENT '接收时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠码表';
        ");
        } else {
            $this->addSql("
              ALTER TABLE `coupon` CHANGE `batchId` `batchId` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '批次号';
            ");
            $this->addSql("
              ALTER TABLE `coupon` CHANGE `targetType` `targetType` varchar(64) NUll DEFAULT NULL COMMENT '使用对象类型';
            ");
            $this->addSql("
              ALTER TABLE `coupon` CHANGE `targetId` `targetId` INT(10) UNSIGNED NULL DEFAULT 0 COMMENT '使用对象';
            ");
            $this->addSql("
              ALTER TABLE `coupon` CHANGE `status` `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态';
            ");

            if (!$this->isFieldExist('coupon', 'receiveTime')) {
                $this->addSql("
              ALTER TABLE `coupon` ADD `receiveTime` INT(10) NULL DEFAULT 0 COMMENT '接收时间';
            ");
            }
        }

        if (!$this->isTableExist('invite_record')) {
            $this->addSql("
            CREATE TABLE `invite_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `inviteUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者',
                `invitedUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者',
                `inviteTime` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请时间',
                `inviteUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者获得奖励的卡的ID',
                `invitedUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者获得奖励的卡的ID',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='邀请记录表';
            ");
        }

        if (!$this->isFieldExist('user', 'inviteCode')) {
            $this->addSql("
                ALTER TABLE `user` ADD `inviteCode` varchar(255) NUll DEFAULT NUll COMMENT '邀请码';
            ");
        }
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

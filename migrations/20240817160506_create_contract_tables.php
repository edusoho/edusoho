<?php

use Phpmig\Migration\Migration;

class CreateContractTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `contract` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL COMMENT '合同名称',
              `content` mediumtext COMMENT '合同内容',
              `seal` varchar(255) NOT NULL COMMENT '甲方印章图标',
              `sign` varchar(255) NOT NULL COMMENT '乙方签署内容',
              `createdUserId` int(10) UNSIGNED NOT NULL COMMENT '创建人',
              `updatedUserId` int(10) UNSIGNED NOT NULL COMMENT '更新人',
              `createdTime` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) UNSIGNED NOT NULL COMMENT '最后更新时间',
              PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '电子合同表';

          CREATE TABLE IF NOT EXISTS `contract_goods_relation` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `goodsKey` varchar(32) NOT NULL COMMENT '商品类型(course,classroom,itemBankExercise)_对应商品id',
              `contractId` int(10) UNSIGNED NOT NULL COMMENT '合同id',
              `sign` tinyint(1) NOT NULL COMMENT '签署要求 0: 非强制, 1: 强制',
              `createdTime` int(10) UNSIGNED NOT NULL,
              `updatedTime` int(10) UNSIGNED NOT NULL,
              PRIMARY KEY (`id`),
              KEY `contractId` (`contractId`),
              KEY `goodsKey` (`goodsKey`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '商品合同关系表';

          CREATE TABLE IF NOT EXISTS `contract_snapshot` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL COMMENT '合同名称',
              `content` mediumtext COMMENT '合同内容',
              `seal` varchar(255) NOT NULL COMMENT '甲方印章图标',
              `version` varchar(32) NOT NULL COMMENT '版本号(MD5)',
              `createdTime` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `version` (`version`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '合同快照表';

          CREATE TABLE IF NOT EXISTS `contract_sign_record` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `userId` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
              `goodsKey` varchar(32) NOT NULL COMMENT '商品类型(course,classroom,itemBankExercise)_对应商品id',
              `snapshot` varchar(1024) COMMENT '签署快照',
              `createdTime` int(10) UNSIGNED NOT NULL,
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `goodsKey` (`goodsKey`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT = '合同签署记录表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('
          DROP TABLE IF EXISTS `contract`;
          DROP TABLE IF EXISTS `contract_goods_relation`;
          DROP TABLE IF EXISTS `contract_sign_record`;
        ');
    }

    private function getConnection()
    {
        return $this->getContainer()->offsetGet('db');
    }
}

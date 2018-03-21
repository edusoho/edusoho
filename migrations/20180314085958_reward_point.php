<?php

use Phpmig\Migration\Migration;

class RewardPoint extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE  IF NOT EXISTS  `reward_point_account` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
              `balance` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分余额',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分账户';
            
            CREATE TABLE  IF NOT EXISTS `reward_point_account_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'inflow, outflow',
              `way` varchar(255) NOT NULL DEFAULT '' COMMENt '积分获取方式',
              `amount` int(10) NOT NULL DEFAULT 0 COMMENT '金额(积分)',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `operator` int(10) unsigned NOT NULL COMMENT '操作员ID',
              `targetId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '流水所属对象ID',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '流水所属对象类型',
              `note` varchar(255) NOT NULL DEFAULT '',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分帐目流水';
        ");

        $result = $biz['db']->fetchAssoc("DESCRIBE `course_v8` `rewardPoint`;");
        if(!$result) {
            $biz['db']->exec("
                ALTER TABLE `course_v8` 
                ADD COLUMN `rewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '课程积分',
                ADD COLUMN `taskRewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '任务积分';
            ");
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `reward_point_account`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `reward_point_account_flow`;');
        $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `taskRewardPoint`, DROP COLUMN `rewardPoint`;');
    }
}

<?php

use Phpmig\Migration\Migration;

class RewardPointAddFlowAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `reward_point_account` ADD `inflowAmount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '入账积分总数' AFTER `balance`;
            ALTER TABLE `reward_point_account` ADD `outflowAmount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '出账积分总数' AFTER `balance`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `reward_point_account` DROP COLUMN `inflowAmount`;
            ALTER TABLE `reward_point_account` DROP COLUMN `outflowAmount`;
        ');
    }
}

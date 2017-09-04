<?php

use Phpmig\Migration\Migration;

class InviteAddOrderRelativeField extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        //2.5秒 100万的数据
        $db->exec('ALTER TABLE `orders` ADD INDEX userId ( `userId` )');
        $db->exec('ALTER TABLE `invite_record` ADD INDEX inviteUserId ( `inviteUserId` )');

        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `amount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `cashAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的现金消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `coinAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的虚拟币消费总额'");

        $time = time();
        $db->exec(
            "INSERT INTO `biz_job`
            (`name`, `source`, `expression`, `class`, `args`, `misfire_policy`, `updated_time`, `created_time`) 
            VALUES 
            ('UpdateInviteRecordOrderInfoJob', 'MAIN', '*/1 * * * *', 'Biz\\\\User\\\\Job\\\\UpdateInviteRecordOrderInfoJob', '', 'missed', {$time}, {$time});
            "
        );
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `invite_record` DROP INDEX `inviteUserId`');
        $db->exec('ALTER TABLE `orders` DROP INDEX `userId`');
        $db->exec("DELETE FROM `biz_job` WHERE `name` = 'UpdateInviteRecordOrderInfoJob' and `source`='MAIN'");
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `amount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `cashAmount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `coinAmount`;');
    }
}

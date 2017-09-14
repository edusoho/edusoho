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
        $db->exec('ALTER TABLE `orders` ADD INDEX idx_userId ( `userId` )');
        $db->exec('ALTER TABLE `invite_record` ADD INDEX idx_inviteUserId ( `inviteUserId` )');

        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `amount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `cashAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的现金消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `coinAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的虚拟币消费总额'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `invite_record` DROP INDEX idx_inviteUserId');
        $db->exec('ALTER TABLE `orders` DROP INDEX idx_userId');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `amount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `cashAmount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `coinAmount`;');
    }
}

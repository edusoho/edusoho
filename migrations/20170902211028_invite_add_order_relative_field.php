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
        $db->exec("ALTER TABLE `orders` ADD INDEX userId ( `userId` )");

        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `amount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `cash_amount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的现金消费总额'");
        $db->exec("ALTER TABLE `invite_record` ADD COLUMN `coin_amount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的虚拟币消费总额'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `amount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `cash_amount`;');
        $db->exec('ALTER TABLE `invite_record` DROP COLUMN `coin_amount`;');
    }
}

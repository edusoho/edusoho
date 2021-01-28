<?php

use Phpmig\Migration\Migration;

class AddMemberRecordOperationReasonType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `reason_type` varchar(255) NOT NULL default '' COMMENT '用户退出或加入的类型：refund, remove, exit' after `reason`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `reason_type`;');
    }
}

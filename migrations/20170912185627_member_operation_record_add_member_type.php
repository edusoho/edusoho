<?php

use Phpmig\Migration\Migration;

class MemberOperationRecordAddMemberType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `member_type`  varchar(32) NOT NULL DEFAULT 'student' COMMENT '成员身份' AFTER `member_id`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `reason`  varchar(256) NOT NULL DEFAULT '' COMMENT '加入理由或退出理由' AFTER `data`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `refunded`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否退款' AFTER `data`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `member_type`;');
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `reason`;');
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `refunded`;');
    }
}

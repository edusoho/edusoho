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
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `member_type`;');
    }
}

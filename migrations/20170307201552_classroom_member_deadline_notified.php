<?php

use Phpmig\Migration\Migration;

class ClassroomMemberDeadlineNotified extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `classroom_member` ADD `deadlineNotified` int(10) NOT NULL DEFAULT '0' COMMENT '有效期通知'; 
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `classroom_member` DROP COLUMN `deadlineNotified`');
    }
}

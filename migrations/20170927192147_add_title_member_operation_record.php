<?php

use Phpmig\Migration\Migration;

class AddTitleMemberOperationRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `title` varchar(1024) NOT NULL default '' COMMENT '标题' after `id`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `title`;');
    }
}

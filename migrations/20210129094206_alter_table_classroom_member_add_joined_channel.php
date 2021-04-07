<?php

use Phpmig\Migration\Migration;

class AlterTableClassroomMemberAddJoinedChannel extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $biz['db']->exec("ALTER TABLE `classroom_member` ADD `joinedChannel` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `orderId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `joinedChannel`;');
    }
}

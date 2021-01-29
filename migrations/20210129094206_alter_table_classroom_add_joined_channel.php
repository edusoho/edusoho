<?php

use Phpmig\Migration\Migration;

class AlterTableClassroomAddJoinedChannel extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom` ADD `joinedChannel` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `price`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom` DROP COLUMN `joinedChannel`;');
    }
}

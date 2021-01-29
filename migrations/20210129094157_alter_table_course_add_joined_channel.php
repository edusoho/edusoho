<?php

use Phpmig\Migration\Migration;

class AlterTableCourseAddJoinedChannel extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_v8` ADD `joinedChannel` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `price`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `joinedChannel`;');
    }
}

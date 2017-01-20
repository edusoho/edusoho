<?php

use Phpmig\Migration\Migration;

class TaskLength extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->container['db']->exce("ALTER TABLE `course_task` ADD COLUMN `length` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->container['db']->exce("ALTER TABLE `course_task` DROP COLUMN `length`");
    }
}

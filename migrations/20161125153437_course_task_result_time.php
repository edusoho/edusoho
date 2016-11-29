<?php

use Phpmig\Migration\Migration;

class CourseTaskResultTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course_task_result` ADD COLUMN `time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务进行时长（分钟）';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course_task_result` DROP COLUMN `time`");
    }
}

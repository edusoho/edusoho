<?php

use Phpmig\Migration\Migration;

class CourseTaskSeq extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course_task` DROP COLUMN `preTaskId`;
              ALTER TABLE `course_task` ADD COLUMN `seq` INT(10) UNSIGNED NOT NULL AFTER `courseId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}

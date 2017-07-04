<?php

use Phpmig\Migration\Migration;

class AddIndexForCourseTaskResult extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_task_result` ADD INDEX `idx_userId_courseId` (`userId`, `courseId`);');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_task_result` DROP INDEX `idx_userId_courseId`;');
    }
}

<?php

use Phpmig\Migration\Migration;

class CourseTaskResultAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_task_result` ADD INDEX courseId_status (`courseId`, `status`);');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE course_task_result DROP INDEX courseId_status;');
    }
}

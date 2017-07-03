<?php

use Phpmig\Migration\Migration;

class CourseTaskTryView extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
                        CREATE TABLE `course_task_try_view` (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `userId` int(10) NOT NULL,
                          `courseSetId` int(10) NOT NULL,
                          `courseId` int(10) NOT NULL,
                          `taskId` int(10) NOT NULL,
                          `taskType` varchar(50) NOT NULL DEFAULT '' COMMENT 'task.type',
                          `createdTime` int(10) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                  ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `course_task_try_view`;');
    }
}

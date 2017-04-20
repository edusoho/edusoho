<?php

use Phpmig\Migration\Migration;

class CourseTaskView extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
          CREATE TABLE `course_task_view` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `courseSetId` int(10) NOT NULL,
              `courseId` int(10) NOT NULL,
              `taskId` int(10) NOT NULL,
              `fileId` int(10) NOT NULL,
              `userId` int(10) NOT NULL,
              `fileType` VARCHAR(80) NOT NULL ,
              `fileStorage` VARCHAR(80) NOT NULL ,
              `fileSource` varchar(32) NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(' drop table course_task_view');
    }
}

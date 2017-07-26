<?php

use Phpmig\Migration\Migration;

class CreateCourseJobTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('CREATE TABLE `course_job` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `courseId` int(10) unsigned NOT NULL COMMENT \'计划Id\',
            `type` varchar(32) NOT NULL DEFAULT \'\' COMMENT \'任务类型\',
            `data` text COMMENT \'任务参数\',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT=\'课程定时任务表\';
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('DROP TABLE `course_job`');
    }
}

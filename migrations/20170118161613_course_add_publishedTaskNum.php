<?php

use Phpmig\Migration\Migration;

class CourseAddPublishedTaskNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `c2_course` ADD COLUMN `publishedTaskNum` INT(10) DEFAULT '0' COMMENT '已发布的任务数' AFTER `taskNum`;
            CREATE VIEW view_course_task AS
            SELECT count(id) as num, courseid FROM course_task WHERE  STATUS = 'published' GROUP BY courseId
            UPDATE `c2_course` ce, view_course_task vk SET ce.`publishedTaskNum` = vk.`num` WHERE vk.`courseid` = ce.id;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
           ALTER TABLE `c2_course` DROP COLUMN `publishedTaskNum`;
        ");
    }
}

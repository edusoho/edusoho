<?php

use Phpmig\Migration\Migration;

class CourseMemberAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `course_member` ADD COLUMN  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID';
        ");

        $db->exec("
            ALTER TABLE `course_member` MODIFY courseId INT(10) unsigned NOT NULL COMMENT '教学计划ID';
        ");

        $db->exec('
            UPDATE course_member AS cm INNER JOIN c2_course c ON c.id = cm.courseId SET cm.courseSetId=c.courseSetId;
        ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `course_member` DROP COLUMN `courseSetId`;
            ALTER TABLE `course_member` MODIFY courseId INT(10) unsigned NOT NULL COMMENT '课程ID';
        ");
    }
}

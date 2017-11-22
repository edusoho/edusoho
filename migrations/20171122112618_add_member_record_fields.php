<?php

use Phpmig\Migration\Migration;

class AddMemberRecordFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `join_course` tinyint(1) NOT NULL default 0 COMMENT '加入的课程的第一个教学计划，算加入课程' after `operate_type`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `exit_course` tinyint(1) NOT NULL default 0 COMMENT '退出的课程的最后教学计划，算退出课程' after `operate_type`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `course_set_id` int(10) NOT NULL default 0 COMMENT '课程Id' after `target_id`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `parent_id` int(10) NOT NULL default 0 COMMENT '班级课程的被复制的计划Id' after `target_id`");
        $db->exec("update `member_operation_record` as r, `course_v8` as c set r.`course_set_id` = c.courseSetId,r.`parent_id` = c.`parentId` where r.`target_id`=c.id and r.`target_type` = 'course'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('
            ALTER TABLE `member_operation_record` DROP COLUMN `join_course`;
            ALTER TABLE `member_operation_record` DROP COLUMN `exit_course`;
            ALTER TABLE `member_operation_record` DROP COLUMN `course_set_id`;
            ALTER TABLE `member_operation_record` DROP COLUMN `parent_id`;
        ');
    }
}

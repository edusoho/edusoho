<?php

use Phpmig\Migration\Migration;

class CrmAddFields extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `classroom` ADD `updatedTime`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `createdTime`;");
        $db->exec('UPDATE `classroom` set `updatedTime`= createdTime WHERE updatedTime = 0 ; ');

        $db->exec('ALTER TABLE  `orders` ADD  `updatedTime` INT(10) NOT NULL AFTER  `createdTime`; ');
        $db->exec('UPDATE `orders` SET `updatedTime` = (select ifnull(max(createdTime),0) from `order_log` where order_log.orderId = orders.id);');
        $db->exec('UPDATE `orders` set `updatedTime`= createdTime WHERE updatedTime = 0 ;');

        $db->exec(" ALTER TABLE `course_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");

        $db->exec(" ALTER TABLE `classroom_member` ADD `lastLearnTime` INT(10) COMMENT '最后学习时间';");

        $db->exec(" ALTER TABLE `classroom_member` ADD `learnedNum` INT(10) COMMENT '已学课时数'; ");

        $db->exec(' UPDATE `course_member` SET `lastLearnTime` = (SELECT ifnull(max(startTime),0) FROM `course_lesson_learn` WHERE course_member.courseId = course_lesson_learn.courseId AND course_member.userId = course_lesson_learn.userId);');

        $db->exec(" UPDATE `classroom_member` SET `lastLearnTime` = (SELECT ifnull(max(lastLearnTime),0) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom');");

        $db->exec(" UPDATE `classroom_member` SET `learnedNum` = (SELECT ifnull(sum(learnedNum),0) FROM `course_member` WHERE classroom_member.classroomId = course_member.classroomId AND classroom_member.userId = course_member.userId AND course_member.joinedType = 'classroom');");

        $db->exec("ALTER TABLE `course_member` ADD `updatedTime` INT(10) NOT NULL DEFAULT '0'COMMENT '最后更新时间'");

        $db->exec("ALTER TABLE `classroom_member` ADD `updatedTime` INT(10) NOT NULL DEFAULT'0' COMMENT '最后更新时间' ");

        $db->exec('UPDATE `course_member` SET `updatedTime` = `createdTime`');

        $db->exec('UPDATE  `classroom_member` SET `updatedTime` = `createdTime`');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}

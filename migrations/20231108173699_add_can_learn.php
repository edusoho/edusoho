<?php

use Phpmig\Migration\Migration;

class AddCanLearn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_set_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `showable`;
            ALTER TABLE `course_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `showable`;
            ALTER TABLE `item_bank_exercise` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `showable`;
            ALTER TABLE `classroom` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `showable`;
            UPDATE `course_set_v8` set canLearn = 0 where status = 'closed';
            UPDATE course_set_v8 set canLearn = '0' where id in (select courseSetId from classroom_courses where classroomId in (select id from classroom where status = 'closed'));
            UPDATE course_v8 set canLearn = '0' where id in (select courseId from classroom_courses where classroomId in (select id from classroom where status = 'closed'));
            UPDATE course_v8 set canLearn = '0' where courseSetId in (select id from course_set_v8 where status = 'closed');
            UPDATE `course_v8` set canLearn = 0 where status = 'closed';
            UPDATE `item_bank_exercise` set canLearn = 0 where status = 'closed';
            UPDATE `classroom` set canLearn = 0 where status = 'closed';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_set_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `course_v8` DROP COLUMN `canLearn`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `canLearn`;
            ALTER TABLE `classroom` DROP COLUMN `canLearn`;
        ');
    }
}

<?php

use Phpmig\Migration\Migration;

class AddShowable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_set_v8` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `platform`;
            ALTER TABLE `course_v8` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `platform`;
            ALTER TABLE `item_bank_exercise` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示' AFTER `isFree`;
            ALTER TABLE `course_set_v8` ADD COLUMN `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放逻辑展示' AFTER `platform`;
            ALTER TABLE `course_v8` ADD COLUMN `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放逻辑展示' AFTER `platform`;
            update course_set_v8 set display = '0' where id in (select courseSetId from classroom_courses where classroomId in (select id from classroom where showable = '0' or status = 'closed'));
            update course_v8 set display = '0' where id in (select courseId from classroom_courses where classroomId in (select id from classroom where showable = '0' or status = 'closed'));
            update course_v8 set display = '0' where courseSetId in (select id from course_set_v8 where status = 'closed');
            update course_set_v8 set showable = 0, display = 0 where status = 'closed';
            update course_v8 set showable = 0, display = 0 where status = 'closed';
            update item_bank_exercise set showable = 0 where status = 'closed';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_set_v8` DROP COLUMN `showable`;
            ALTER TABLE `course_set_v8` DROP COLUMN `display`;
            ALTER TABLE `course_v8` DROP COLUMN `showable`;
            ALTER TABLE `course_v8` DROP COLUMN `display`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `showable`;
            ALTER TABLE `item_bank_exercise` DROP COLUMN `display`;
        ');
    }
}

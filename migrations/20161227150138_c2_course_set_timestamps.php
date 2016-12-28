<?php

use Phpmig\Migration\Migration;

class C2CourseSetTimestamps extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course_set` CHANGE `created` `createdTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间';
            ALTER TABLE `c2_course_set` CHANGE `updated` `updatedTime` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course_set` CHANGE `createdTime` `created` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间';
            ALTER TABLE `c2_course_set` CHANGE `updatedTime` `updated` INT(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间';
        ");
    }
}

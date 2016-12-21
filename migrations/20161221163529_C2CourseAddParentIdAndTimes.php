<?php

use Phpmig\Migration\Migration;

class C2CourseAddParentIdAndTimes extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
           ALTER TABLE `c2_course` ADD COLUMN `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id';
           ALTER TABLE `c2_course` ADD COLUMN `createdTime` INT(10) UNSIGNED NOT NULL COMMENT '课程创建时间';
           ALTER TABLE `c2_course` ADD COLUMN `updatedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间';
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
            ALTER TABLE `c2_course` DROP COLUMN `parentId`;
            ALTER TABLE `c2_course` DROP COLUMN `updatedTime`;
            ALTER TABLE `c2_course` DROP COLUMN `createdTime`;
            ");
    }
}



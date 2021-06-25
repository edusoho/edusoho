<?php

use Phpmig\Migration\Migration;

class AddAssistantStudent extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `assistant_student` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `assistantId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '助教ID',
              `studentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '学员ID',
              `courseId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '课程ID',
              `multiClassId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '班课ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT 0,
              `updatedTime` int(10) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='助教学员关系表';
        ");
        $biz['db']->exec('
            ALTER TABLE `assistant_student` ADD INDEX `course_assistant` (`assistantId`, `courseId`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `assistant_student`;');
        $biz['db']->exec('
            ALTER TABLE `assistant_student` DROP INDEX `course_assistant`;
        ');
    }
}

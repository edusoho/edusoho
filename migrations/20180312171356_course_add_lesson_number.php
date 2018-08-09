<?php

use Phpmig\Migration\Migration;

class CourseAddLessonNumber extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_v8` ADD COLUMN `lessonNum` int(10)  NOT NULL DEFAULT 0 COMMENT '课时总数' AFTER `compulsoryTaskNum`;");
        $connection->exec("ALTER TABLE `course_v8` ADD COLUMN `publishLessonNum` int(10)  NOT NULL DEFAULT 0 COMMENT '课时发布数量' AFTER `lessonNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `course_v8` DROP COLUMN `lessonNum`;');
        $connection->exec('ALTER TABLE `course_v8` DROP COLUMN `publishLessonNum`;');
    }
}

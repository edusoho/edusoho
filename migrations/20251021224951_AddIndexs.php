<?php

use Phpmig\Migration\Migration;

class AddIndexs extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        if (!$this->isIndexExist('classroom_member', 'userId_classroomId')) {
            $biz['db']->exec('ALTER TABLE `classroom_member` ADD INDEX `userId_classroomId` (`userId`, `classroomId`);');
        }

        if (!$this->isIndexExist('course_task', 'fromCourseSetId')) {
            $biz['db']->exec('ALTER TABLE `course_task` ADD INDEX `fromCourseSetId` (`fromCourseSetId`);');
        }

        if (!$this->isIndexExist('activity', 'fromCourseId')) {
            $biz['db']->exec('ALTER TABLE `activity` ADD INDEX `fromCourseId` (`fromCourseId`);');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        if ($this->isIndexExist('classroom_member', 'userId_classroomId')) {
            $biz['db']->exec('ALTER TABLE `classroom_member` DROP INDEX `userId_classroomId`;');
        }

        if ($this->isIndexExist('course_task', 'fromCourseSetId')) {
            $biz['db']->exec('ALTER TABLE `course_task` DROP INDEX `fromCourseSetId`;');
        }

        if ($this->isIndexExist('activity', 'fromCourseId')) {
            $biz['db']->exec('ALTER TABLE `activity` DROP INDEX `fromCourseId`;');
        }
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }
}

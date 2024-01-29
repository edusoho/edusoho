<?php

use Phpmig\Migration\Migration;

class CourseChapterAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isIndexExist('course_chapter', 'copyId_courseId')) {
            $this->getContainer()['db']->exec('ALTER TABLE `course_chapter` ADD INDEX `copyId_courseId`(`copyId`, `courseId`)');
        }
        if (!$this->isIndexExist('course_chapter', 'courseId')) {
            $this->getContainer()['db']->exec('ALTER TABLE `course_chapter` ADD INDEX `courseId`(`courseId`)');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isIndexExist('course_chapter', 'copyId_courseId')) {
            $this->getContainer()['db']->exec('ALTER TABLE `course_chapter` DROP INDEX `copyId_courseId`');
        }
        if ($this->isIndexExist('course_chapter', 'courseId')) {
            $this->getContainer()['db']->exec('ALTER TABLE `course_chapter` DROP INDEX `courseId`');
        }
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }
}

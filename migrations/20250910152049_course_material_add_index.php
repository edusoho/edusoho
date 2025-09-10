<?php

use Phpmig\Migration\Migration;

class CourseMaterialAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        if (!$this->isIndexExist('course_material_v8', 'fileId_courseSetId')) {
            $biz['db']->exec('ALTER TABLE `course_material_v8` ADD INDEX `fileId_courseSetId` (`fileId`, `courseSetId`);');
        }
        if (!$this->isIndexExist('course_material_v8', 'copyId')) {
            $biz['db']->exec('ALTER TABLE `course_material_v8` ADD INDEX `copyId` (`copyId`);');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        if ($this->isIndexExist('course_material_v8', 'fileId_courseSetId')) {
            $biz['db']->exec('ALTER TABLE `course_material_v8` DROP INDEX `fileId_courseSetId`;');
        }
        if ($this->isIndexExist('course_material_v8', 'copyId')) {
            $biz['db']->exec('ALTER TABLE `course_material_v8` DROP INDEX `copyId`;');
        }
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }
}

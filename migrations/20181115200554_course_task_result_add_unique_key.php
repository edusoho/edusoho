<?php

use Phpmig\Migration\Migration;

class CourseTaskResultAddUniqueKey extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->createUniqueIndex();
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->dropUniqueIndex();
    }

    protected function isIndexExist()
    {
        $sql = "show index from `course_task_result` where Key_name = 'courseTaskId_userId';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function createUniqueIndex()
    {
        if (!$this->isIndexExist()) {
            $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD UNIQUE INDEX `courseTaskId_userId` (`courseTaskId`, `userId`);");
        }
    }

    protected function dropUniqueIndex()
    {
        if ($this->isIndexExist()) {
            $this->getConnection()->exec("DROP INDEX `courseTaskId_userId` ON `course_task_result`;");
        }
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function getBiz()
    {
        return $biz = $this->getContainer();
    }
}

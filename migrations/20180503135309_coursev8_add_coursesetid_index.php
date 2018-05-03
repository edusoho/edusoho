<?php

use Phpmig\Migration\Migration;

class Coursev8AddCoursesetidIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isIndexExist('course_v8', 'courseset_id_index')) {
            $connection->exec('
                ALTER TABLE `course_v8` ADD INDEX `courseset_id_index` (`courseSetId`);
            ');
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }

    protected function isIndexExist($table, $indexName)
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

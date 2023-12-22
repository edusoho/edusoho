<?php

use Phpmig\Migration\Migration;

class CourseChapterAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }

    private function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}`  where Key_name='{$indexName}';";
        $result = $this->getContainer()['db']->fetchAssoc($sql);

        return !empty($result);
    }
}

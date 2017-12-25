<?php

use Phpmig\Migration\Migration;

class AlterAddCourseV8Field extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if (!$this->isFieldExist('course_v8', 'enableAudio')) {
            $db->exec("ALTER TABLE `course_v8` ADD `enableAudio` int(1) NOT NULL DEFAULT '0';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('course_v8', 'enableAudio')) {
            $db->exec('ALTER TABLE `course_v8` DROP `enableAudio`');
        }
    }

    protected function isFieldExist($table, $fieldName)
    {
        $container = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `$fieldName`";
        $result = $container['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

<?php

use Phpmig\Migration\Migration;

class CourseV8AddSubtitleFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_v8` ADD `subtitle` varchar(120) DEFAULT '' COMMENT '计划副标题' AFTER `title`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        if ($this->isFieldExist('course_v8', 'subtitle')) {
            $db->exec('ALTER TABLE `course_v8` DROP `subtitle`;');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

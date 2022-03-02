<?php

use Phpmig\Migration\Migration;

class CourseV8AddTaskDisplay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        if (!$this->isFieldExist('course_v8', 'taskDisplay')) {
            $biz = $this->getContainer();
            $biz['db']->exec("ALTER TABLE `course_v8` ADD COLUMN `taskDisplay` tinyint(1) unsigned  NOT NULL DEFAULT 1 COMMENT '目录展示'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        if ($this->isFieldExist('course_v8', 'taskDisplay')) {
            $biz = $this->getContainer();
            $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `taskDisplay`');
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

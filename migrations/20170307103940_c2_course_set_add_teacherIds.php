<?php

use Phpmig\Migration\Migration;

class C2CourseSetAddTeacherIds extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        //兼容峰哥的方案
        if (!$this->fieldExists('c2_course_set', 'teacherIds')) {
            $biz['db']->exec('
                ALTER TABLE `c2_course_set` ADD `teacherIds` varchar(1024) DEFAULT null;
            ');
        }
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();

        if (!$this->fieldExists('c2_course_set', 'teacherIds')) {
            $biz['db']->exec('
                ALTER TABLE `c2_course_set` DROP COLUMN `teacherIds`;
            ');
        }
    }

    private function fieldExists($table, $field)
    {
        $biz = $this->getContainer();
        $sql = "DESCRIBE `{$table}` `{$field}`;";
        $result = $biz['db']->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

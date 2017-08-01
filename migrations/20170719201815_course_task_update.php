<?php

use Phpmig\Migration\Migration;

class CourseTaskUpdate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('ALTER TABLE `course_task` CHANGE `seq` `seq` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'序号\'');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}

<?php

use Phpmig\Migration\Migration;

class CourseTaskNumber extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE	`course_task` ADD COLUMN `number` int(10) unsigned NOT NULL");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("ALTER TABLE	`course_task` DROP COLUMN `number`");
    }
}

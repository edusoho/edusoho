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
        $db->exec("
            ALTER TABLE	`course_task` ADD COLUMN `number` int(10) unsigned NOT NULL;
            ALTER TABLE	`course_task` ADD COLUMN `mode` VARCHAR(60) NULL COMMITTED  '任务模式';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE	`course_task` DROP COLUMN `number`;
            ALTER TABLE	`course_task` DROP COLUMN `mode`;
        ");
    }
}

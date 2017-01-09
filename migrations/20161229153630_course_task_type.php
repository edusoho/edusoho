<?php

use Phpmig\Migration\Migration;

class CourseTaskType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `course_task` ADD COLUMN  `type` VARCHAR(50) NOT NULL COMMENT '任务类型';
            ALTER TABLE `course_task` ADD	INDEX  `seq` (`seq`);
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
            ALTER TABLE `course_task` DROP COLUMN `type`;
        ");
    }
}

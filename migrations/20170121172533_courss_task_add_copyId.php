<?php

use Phpmig\Migration\Migration;

class CourssTaskAddCopyId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task` ADD COLUMN `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制来源task的id';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task` DROP COLUMN `copyId`;");
    }
}

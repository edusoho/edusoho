<?php

use Phpmig\Migration\Migration;

class C2CourseClone extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
             ALTER TABLE `c2_course` ADD COLUMN  `cloneId` int(10) DEFAULT 0 COMMENT '教学计划的复制来源';
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
             ALTER TABLE `c2_course` DROP COLUMN  `cloneId`;
        ");
    }
}

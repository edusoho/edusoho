<?php

use Phpmig\Migration\Migration;

class CourseMaxRate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
             ALTER TABLE `c2_course` ADD COLUMN  `maxRate` tinyint(3) DEFAULT 0 COMMENT '最大抵扣百分比';
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
             ALTER TABLE `c2_course` DROP COLUMN  `maxRate`;
        ");
    }
}

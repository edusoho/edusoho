<?php

use Phpmig\Migration\Migration;

class C2CourseSetAddLocked extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD COLUMN `locked` INT(10) NOT NULL DEFAULT '0' COMMENT '是否锁定，1=锁定，0=不锁定';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `locked`;");
    }
}

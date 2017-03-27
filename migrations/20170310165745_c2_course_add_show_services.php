<?php

use Phpmig\Migration\Migration;

class C2CourseAddShowServices extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` ADD COLUMN `showServices` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否在营销页展示服务承诺'; 
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `c2_course` DROP COLUMN `showServices`;');
    }
}

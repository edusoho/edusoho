<?php

use Phpmig\Migration\Migration;

class C2CourseAddEnableFinish extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db  = $biz['db'];
        $db->exec("
            ALTER TABLE `c2_course` ADD COLUMN  `enableFinish` INT(1) NOT NULL DEFAULT '1' COMMENT '是否允许学院强制完成任务';
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
            ALTER TABLE `c2_course` DROP COLUMN  `enableFinish`;
        ");
    }
}

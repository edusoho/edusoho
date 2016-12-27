<?php

use Phpmig\Migration\Migration;

class C2CourseTimestamps extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` DROP COLUMN created;
            ALTER TABLE `c2_course` DROP COLUMN updated;
            ALTER TABLE `c2_course` DROP COLUMN deleted;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` ADD COLUMN created int(11) DEFAULT 0 COMMENT '创建时间';
            ALTER TABLE `c2_course` ADD COLUMN updated int(11) DEFAULT 0 COMMENT '更新时间';
            ALTER TABLE `c2_course` ADD COLUMN deleted int(11) DEFAULT 0 COMMENT '删除时间';
        ");
    }
}

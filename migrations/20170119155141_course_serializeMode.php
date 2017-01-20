<?php

use Phpmig\Migration\Migration;

class CourseSerializeMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(" ALTER TABLE `c2_course` ADD COLUMN `serializeMode` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec(" ALTER TABLE `c2_course` DROP COLUMN `serializeMode`; ");
    }
}

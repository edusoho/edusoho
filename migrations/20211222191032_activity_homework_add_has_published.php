<?php

use Phpmig\Migration\Migration;

class ActivityHomeworkAddHasPublished extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_homework` ADD COLUMN `has_published` tinyint NOT NULL DEFAULT 0 COMMENT '是否发布过';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_homework` DROP COLUMN `has_published`;');
    }
}

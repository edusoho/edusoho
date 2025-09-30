<?php

use Phpmig\Migration\Migration;

class ActivityLiveAddTeacherId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_live` ADD COLUMN `teacherId` int(10) NOT NULL DEFAULT 0 COMMENT '直播主讲老师'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_live` DROP COLUMN `teacherId`');
    }
}

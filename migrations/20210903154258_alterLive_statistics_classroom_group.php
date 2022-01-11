<?php

use Phpmig\Migration\Migration;

class AlterLiveStatisticsClassroomGroup extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `live_statistics` ADD COLUMN `classroomGroupId` int(10) NOT NULL DEFAULT 0 COMMENT '班级分组id' AFTER `liveId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `live_statistics` DROP COLUMN `classroomGroupId`;');
    }
}

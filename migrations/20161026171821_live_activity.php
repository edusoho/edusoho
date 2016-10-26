<?php

use Phpmig\Migration\Migration;

class LiveActivity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `live_activity` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
          `time_last` int(11) DEFAULT NULL COMMENT '持续时长',
          `fromCourseId` int(11) DEFAULT NULL COMMENT '课程ID',
          `fromCourseSetId` int(11) DEFAULT NULL COMMENT '课程计划ID',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}

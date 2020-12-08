<?php

use Phpmig\Migration\Migration;

class DataVisualizationAddUserDailyTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `user_stay_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `user_video_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `user_stay_daily`;
        ');

        $biz['db']->exec('
            DROP TABLE IF EXISTS `user_video_daily`;
        ');
    }
}

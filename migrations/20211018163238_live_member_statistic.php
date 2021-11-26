<?php

use Phpmig\Migration\Migration;

class LiveMemberStatistic extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('CREATE TABLE IF NOT EXISTS `live_statistics_member_data` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `liveId` int(11) DEFAULT NULL,
                              `firstEnterTime` int(11) DEFAULT NULL,
                              `watchDuration` int(11) DEFAULT 0,
                              `checkinNum` int(11) DEFAULT 0,
                              `createdTime` int(11) DEFAULT NULL,
                              `updatedTime` int(11) DEFAULT NULL,
                              `requestTime` int(11) DEFAULT 0,
                              `userId` int(11) DEFAULT 0,
                              `courseId` int(11) DEFAULT 0,
                              `chatNum` int(11) DEFAULT 0,
                              `answerNum` int(11) DEFAULT 0,
                              UNIQUE KEY `courseId_userId` (`courseId`,`userId`),
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
        $biz['db']->exec("
            ALTER TABLE `activity_live` ADD COLUMN `cloudStatisticData` text COMMENT '直播数据' ;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `live_statistics_member_data`;');
        $biz['db']->exec('ALTER TABLE `activity_live` DROP COLUMN `cloudStatisticData`;');
    }
}

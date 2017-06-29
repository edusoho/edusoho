<?php

use Phpmig\Migration\Migration;

class TestpaperActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `testpaper_activity` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '关联activity表的ID',
              `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷ID',
              `doTimes` smallint(6) NOT NULL DEFAULT '0' COMMENT '考试次数',
              `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)',
              `limitedTime` int(10) NOT NULL DEFAULT '0' COMMENT '考试时间',
              `checkType` text,
              `finishCondition` text,
              `requireCredit` int(10) NOT NULL DEFAULT '0' COMMENT '参加考试所需的学分',
              `testMode` varchar(50) NOT NULL DEFAULT 'normal' COMMENT '考试模式',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        /*$connection->exec("ALTER TABLE testpaper ADD `courseId` int(10) NOT NULL DEFAULT '0' AFTER `description`");
    $connection->exec("ALTER TABLE testpaper ADD `lessonId` int(10) NOT NULL DEFAULT '0' AFTER `courseId`");
    $connection->exec("ALTER TABLE testpaper ADD `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';");

    $connection->exec('ALTER TABLE testpaper CHANGE passedScore passedCondition text');

    $connection->exec("ALTER TABLE testpaper_item_result CHANGE testPaperResultId resultId int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID'");

    $connection->exec("ALTER TABLE testpaper_result ADD `courseId` int(10) NOT NULL DEFAULT '0' AFTER `userId`");
    $connection->exec("ALTER TABLE testpaper_result ADD `lessonId` int(10) NOT NULL DEFAULT '0' AFTER `courseId`");
    $connection->exec("ALTER TABLE testpaper_result ADD `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型';");*/
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `testpaper_activity`');

        /*$connection->exec('ALTER TABLE testpaper DROP column `courseId`');
    $connection->exec('ALTER TABLE testpaper DROP column `lessonId`');
    $connection->exec('ALTER TABLE testpaper DROP column `type`');

    $connection->exec('ALTER TABLE testpaper_result DROP column `courseId`');
    $connection->exec('ALTER TABLE testpaper_result DROP column `lessonId`');
    $connection->exec('ALTER TABLE testpaper_result DROP column `type`');

    $connection->exec("ALTER TABLE testpaper CHANGE passedCondition passedScore float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '通过考试的分数线'");

    $connection->exec("ALTER TABLE testpaper_item_result CHANGE resultId testPaperResultId int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID'");*/
    }
}

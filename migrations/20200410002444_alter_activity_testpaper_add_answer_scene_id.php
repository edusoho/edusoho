<?php

use Phpmig\Migration\Migration;

class AlterActivityTestpaperAddAnswerSceneId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
           ALTER TABLE `activity_testpaper` ADD `answerSceneId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '答题引擎场次id' AFTER `testMode`;
           ALTER TABLE `activity_testpaper` ADD INDEX(`answerSceneId`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            ALTER TABLE `activity_testpaper` DROP `answerSceneId`;
        ');
    }
}

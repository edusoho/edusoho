<?php

use Phpmig\Migration\Migration;

class ActivityTestpaperAddAnswerMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_testpaper` ADD COLUMN `answerMode` TINYINT NOT NULL DEFAULT 0 COMMENT '答案显示模式: 1:合格后显示答案;' AFTER doTimes");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_testpaper` DROP COLUMN `answerMode`');
    }
}

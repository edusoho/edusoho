<?php

use Phpmig\Migration\Migration;

class AlterMultiClass extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `multi_class` ADD COLUMN `maxStudentNum` int(11) NOT NULL DEFAULT '0' COMMENT '限购人数' AFTER productId;");
        $biz['db']->exec("ALTER TABLE `multi_class` ADD COLUMN `isReplayShow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '回放是否显示' AFTER maxStudentNum;");
        $biz['db']->exec("ALTER TABLE `multi_class` ADD COLUMN `liveRemindTime` int(11) NOT NULL DEFAULT '0' COMMENT '直播提醒时间（分钟）' AFTER isReplayShow;");
        $biz['db']->exec("ALTER TABLE `multi_class` ADD COLUMN `creator` int(11) NOT NULL DEFAULT '0' COMMENT '创建者' AFTER copyId;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `multi_class` DROP COLUMN `maxStudentNum`;');
        $biz['db']->exec('ALTER TABLE `multi_class` DROP COLUMN `isReplayShow`;');
        $biz['db']->exec('ALTER TABLE `multi_class` DROP COLUMN `liveRemindTime`;');
        $biz['db']->exec('ALTER TABLE `multi_class` DROP COLUMN `creator`;');
    }
}

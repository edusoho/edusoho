<?php

use Phpmig\Migration\Migration;

class ActivityLearnLogChangeEvent extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `activity_learn_log` CHANGE `event` `event` VARCHAR(32) NOT NULL COMMENT '事件类型';
            ALTER TABLE `activity_learn_log` ADD COLUMN `mediaType` VARCHAR(32) NOT NULL COMMENT '活动类型';
            UPDATE `activity_learn_log` SET mediaType = LEFT(`event`, LOCATE('.',`event`) - 1), `event` = SUBSTRING(`event`, LOCATE('.',`event`) + 1) WHERE `event` IS NOT NULL;
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            ALTER TABLE `activity_learn_log` CHANGE `event` `event` VARCHAR(255) NOT NULL COMMENT '事件类型';
            UPDATE `activity_learn_log` SET `event` = concat(`mediaType`, '.', `event`) WHERE `event` IS NOT NULL;
            ALTER TABLE `activity_learn_log` DROP COLUMN `mediaType`;
        ");
    }
}

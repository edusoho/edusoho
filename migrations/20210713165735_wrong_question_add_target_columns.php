<?php

use Phpmig\Migration\Migration;

class WrongQuestionAddTargetColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_wrong_question` ADD COLUMN `source_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID' AFTER submit_time;");
        $biz['db']->exec("ALTER TABLE `biz_wrong_question` ADD COLUMN `source_type` varchar(32) DEFAULT '' COMMENT '来源类型' AFTER submit_time;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_wrong_question` DROP COLUMN `source_id`;");
        $biz['db']->exec("ALTER TABLE `biz_wrong_question` DROP COLUMN `source_type`;");
    }
}

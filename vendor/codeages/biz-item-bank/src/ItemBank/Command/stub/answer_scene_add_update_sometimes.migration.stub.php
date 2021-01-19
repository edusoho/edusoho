<?php

use Phpmig\Migration\Migration;

class BizAnswerSceneAddUpdateSometimes extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `last_review_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '考题最后批阅沟通时间' AFTER `updated_user_id`;");
        $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `question_report_update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次题目分析最后生成时间' AFTER `updated_user_id`;");
        $biz['db']->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `question_report_job_name` varchar(256) DEFAULT '' COMMENT '场次分析JOB Name';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_answer_scene` DROP COLUMN `last_review_time`;');
        $biz['db']->exec('ALTER TABLE `biz_answer_scene` DROP COLUMN `question_report_update_time`;');
        $biz['db']->exec('ALTER TABLE `biz_answer_scene` DROP COLUMN `question_report_job_name`;');
    }
}

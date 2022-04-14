<?php

use Phpmig\Migration\Migration;

class BizAnswerQuestionReportAddRevise extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_answer_question_report` ADD COLUMN `revise` text COMMENT '纠正';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_answer_question_report` DROP COLUMN `revise`;');
    }
}

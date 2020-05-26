<?php

use Phpmig\Migration\Migration;

class AddIndexIdentifyBizAnswerQuestionReport extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            ALTER TABLE `biz_answer_question_report` ADD INDEX(`identify`);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}

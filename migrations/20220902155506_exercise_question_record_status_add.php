<?php

use Phpmig\Migration\Migration;

class ExerciseQuestionRecordStatusAdd extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER  TABLE `item_bank_exercise_question_record` MODIFY COLUMN `status` enum('right','wrong', 'part_right', 'reviewing') NOT NULL DEFAULT 'wrong' COMMENT '状态';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}

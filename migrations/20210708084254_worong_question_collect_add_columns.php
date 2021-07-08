<?php

use Phpmig\Migration\Migration;

class WorongQuestionCollectAddColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_wrong_question_collect` ADD COLUMN `status` varchar(32) DEFAULT 'wrong' COMMENT '题目状态： wrong，correct';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `biz_wrong_question_collect` DROP COLUMN `status`;');
    }
}

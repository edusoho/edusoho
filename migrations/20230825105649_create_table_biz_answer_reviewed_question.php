<?php

use Phpmig\Migration\Migration;

class CreateTableBizAnswerReviewedQuestion extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `biz_answer_reviewed_question` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `question_id` int(10) unsigned NOT NULL COMMENT '问题id',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_answer_reviewed_question`;');
    }
}

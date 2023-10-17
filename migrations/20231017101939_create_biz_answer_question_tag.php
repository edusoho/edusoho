<?php

use Phpmig\Migration\Migration;

class CreateBizAnswerQuestionTag extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `biz_answer_question_tag` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `tag_question_ids` text NOT NULL COMMENT '标记问题id数组',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_answer_question_tag`;');
    }
}

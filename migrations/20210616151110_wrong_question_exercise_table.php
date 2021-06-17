<?php

use Phpmig\Migration\Migration;

class WrongQuestionExerciseTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `biz_wrong_book_exercise` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `answer_scene_id` int(11) unsigned NOT NULL COMMENT '场次ID',
              `assessment_id` int(11) unsigned NOT NULL COMMENT '试卷ID',
              `regulation` text COMMENT '做题规则',
              `user_id` int(11) unsigned NOT NULL COMMENT '做题人',
              `created_time` int(11) unsigned NOT NULL DEFAULT '0',
              `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_wrong_book_exercise`;');
    }
}

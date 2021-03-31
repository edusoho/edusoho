<?php

use Phpmig\Migration\Migration;

class AddTableBizAnswerSceneQuestionReport extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `biz_answer_scene_question_report` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `answer_scene_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次id',
                `question_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题id',
                `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
                `right_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答对人数',
                `wrong_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答错人数',
                `no_answer_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未作答人数',
                `part_right_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部分正确人数',
                `response_points_report` text COMMENT '输入点报告',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `answer_scene_id` (`answer_scene_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='场次报告';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('
            DROP TABLE IF EXISTS `biz_question_favorite`;
        ');
    }
}

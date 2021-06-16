<?php

use Phpmig\Migration\Migration;

class BizItemWrongQuestionBookTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `biz_wrong_question` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `collect_id` int(11) unsigned NOT NULL COMMENT '题目集合ID',
              `user_id` int(11) unsigned NOT NULL COMMENT '错题用户ID',
              `item_id` int(11) unsigned NOT NULL COMMENT 'biz_item ID ',
              `question_id` int(11) unsigned NOT NULL COMMENT 'biz_question ID ',
              `answer_scene_id` int(11) unsigned NOT NULL COMMENT '场次ID',
              `answer_question_report_id` int(11) unsigned NOT NULL COMMENT '题目报告ID',
              `submit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '提交时间',
              `created_time` int(11) unsigned NOT NULL DEFAULT '0',
              `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题记录表';
        ");

        $biz['db']->exec("
            CREATE TABLE `biz_wrong_question_collect` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `pool_id` int(11) unsigned NOT NULL COMMENT '池子ID',
              `item_id` int(11) unsigned NOT NULL COMMENT 'biz_item ID',
              `wrong_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '错误频次',
              `last_submit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后错题时间',
              `created_time` int(11) unsigned NOT NULL DEFAULT '0',
              `updated_time` int(11) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题统计集合表';
        ");

        $biz['db']->exec("
            CREATE TABLE `biz_wrong_question_book_pool` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(11) unsigned NOT NULL COMMENT '错题用户ID',
              `item_num` int(11) unsigned NOT NULL COMMENT '错题数量',
              `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '所属类型',
              `target_id` int(11) unsigned NOT NULL COMMENT '所属ID',
              `created_time` int(11) NOT NULL DEFAULT '0',
              `updated_time` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错题池';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_wrong_question`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_wrong_question_collect`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `biz_wrong_question_book_pool`;');
    }
}

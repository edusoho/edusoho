<?php

use Phpmig\Migration\Migration;

class AddTableBizQuestionFavorite extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `biz_question_favorite` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '收藏来源',
                `target_id` int(10) NOT NULL DEFAULT '0' COMMENT '收藏来源id',
                `question_id` int(10) NOT NULL DEFAULT '0' COMMENT '问题id',
                `item_id` int(10) NOT NULL DEFAULT '0' COMMENT '题目id',
                `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户Id',
                `created_time` int(10) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `target_type_and_target_id` (`target_type`,`target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目收藏表';
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

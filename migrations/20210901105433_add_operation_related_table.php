<?php

use Phpmig\Migration\Migration;

class AddOperationRelatedTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `operation_detail_statistic` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `version` varchar(32) NOT NULL COMMENT '网校版本',
                `operator_id` int(10) unsigned NOT NULL COMMENT '操作人id',
                `target_id` int(10) unsigned NOT NULL COMMENT '操作对象id',
                `target_type` varchar(64) NOT NULL COMMENT '操作类型',
                `data` text COMMENT '操作数据详情（json格式）',
                `created_time` int(10) unsigned NOT NULL COMMENT '操作时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='操作详情统计';

            CREATE TABLE IF NOT EXISTS `operation_count_statistic` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `version` varchar(32) NOT NULL COMMENT '网校版本',
                `operator_id` int(10) unsigned NOT NULL COMMENT '操作人id',
                `target_type` varchar(64) NOT NULL COMMENT '操作类型',
                `operation_num` int(10) unsigned NOT NULL COMMENT '操作次数',
                `created_time` int(10) unsigned NOT NULL COMMENT '操作时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='操作次数统计';   
        ");

        $biz['db']->exec("
            ALTER TABLE `activity_testpaper` ADD COLUMN `comment_data` text COMMENT '评语详情数据（json格式）' after `finishCondition`;
            ALTER TABLE `activity_homework` ADD COLUMN `finish_condition` text COMMENT '完成条件' after `assessmentId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE `operation_detail_statistic`;
            DROP TABLE `operation_count_statistic`;
        ');
        $biz['db']->exec('
            ALTER TABLE `activity_testpaper` DROP COLUMN `comment_data`;
            ALTER TABLE `activity_homework` DROP COLUMN `finish_condition`;
        ');
    }
}

<?php

use Phpmig\Migration\Migration;

class AddActivityReplay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `activity_replay`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `finish_type` varchar(32) NOT NULL DEFAULT 'end' COMMENT '完成类型',
                  `finish_detail` varchar(32) NOT NULL DEFAULT '' COMMENT '完成条件',
                  `origin_lesson_id` int(10) NOT NULL DEFAULT 0 COMMENT '引用课时ID',
                  `created_time` int(10) NOT NULL COMMENT '创建时间',
                  `updated_time` int(10) NOT NULL COMMENT '最后更新时间',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '直播回放活动';

             CREATE TABLE IF NOT EXISTS `classroom_live_group`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `classroom_id` int(10) NOT NULL COMMENT '班级ID',
                  `live_code` varchar(64) NOT NULL DEFAULT '' COMMENT '直播分组ID',
                  `live_id` int(10) NOT NULL DEFAULT 0 COMMENT '直播ID',
                  `created_time` int(10) NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '班级直播分组';

             CREATE TABLE IF NOT EXISTS `lesson_marker`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `lesson_id` int(10) NOT NULL DEFAULT 0 COMMENT '教学活动ID',
                  `marker_id` int(10) NOT NULL DEFAULT 0 COMMENT '驻点ID',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '课时弹题';
            ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE `activity_replay`;
            DROP TABLE `classroom_live_group`;
            DROP TABLE `lesson_marker`;
        ');
    }
}

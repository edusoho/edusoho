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
                  `finishType` varchar(32) NOT NULL DEFAULT 'end' COMMENT '完成类型',
                  `finishDetail` varchar(32) NOT NULL DEFAULT '' COMMENT '完成条件',
                  `originLessonId` int(10) NOT NULL DEFAULT 0 COMMENT '引用课时ID',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '直播回放活动';

             CREATE TABLE IF NOT EXISTS `classroom_live_group`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `classroomId` int(10) NOT NULL COMMENT '班级ID',
                  `liveCode` varchar(64) NOT NULL DEFAULT '' COMMENT '直播分组ID',
                  `liveId` int(10) NOT NULL DEFAULT 0 COMMENT '直播ID',
                  `createdTime` int(10) NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`)
                  )ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = '班级直播分组';

             CREATE TABLE IF NOT EXISTS `lesson_marker`  (
                  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `lessonId` int(10) NOT NULL DEFAULT end COMMENT '教学活动ID',
                  `markerId` int(10) NOT NULL DEFAULT 0 COMMENT '驻点ID',
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

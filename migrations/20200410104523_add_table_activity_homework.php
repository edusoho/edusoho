<?php

use Phpmig\Migration\Migration;

class AddTableActivityHomework extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `activity_homework` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `answerSceneId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次ID',
            `assessmentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷id',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `answerSceneId` (`answerSceneId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='作业活动表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ADROP TABLE IF EXISTS `activity_homework`;
        ");
    }
}

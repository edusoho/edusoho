<?php

use Phpmig\Migration\Migration;

class AddTableActivityExercise extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `activity_exercise` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `answerSceneId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '场次ID',
              `drawCondition` TEXT COMMENT '抽题条件',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='练习活动表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            DROP TABLE IF EXISTS `activity_exercise`;
        ');
    }
}

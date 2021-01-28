<?php

use Phpmig\Migration\Migration;

class QuestionAnalysis extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            CREATE TABLE IF NOT EXISTS `question_analysis` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `targetId` int(10) unsigned NOT NULL DEFAULT '0',
              `targetType` varchar(30) NOT NULL,
              `activityId` int(10) unsigned NOT NULL DEFAULT '0',
              `questionId` int(10) unsigned NOT NULL,
              `choiceIndex` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选项key',
              `firstAnswerCount` int(10) unsigned NOT NULL DEFAULT '0',
              `totalAnswerCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '全部答题人数',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='答题分析表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec('
            DROP TABLE `question_analysis`;
        ');
    }
}

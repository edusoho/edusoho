<?php

use Phpmig\Migration\Migration;

class AddQuestionBank extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `question_bank` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(1024) NOT NULL COMMENT '题库名称',
              `testpaperNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷数量',
              `questionNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目数量',
              `categoryId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类id',
              `orgId` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '组织机构id',
              `orgCode` varchar(265) NOT NULL DEFAULT '1.' COMMENT '组织机构编码',
              `isHidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `fromCourseSetId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程id',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库表';
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
            DROP TABLE IF EXISTS `question_bank`;
        ');
    }
}

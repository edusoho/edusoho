<?php

use Phpmig\Migration\Migration;

class AddQuestionCategory extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            DROP TABLE IF EXISTS `question_category`;
        ');
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `question_category` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(1024) NOT NULL COMMENT '名称',
              `weight` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权重',
              `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级分类id',
              `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id',
              `userId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新用户id',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题目分类表';
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
            DROP TABLE IF EXISTS `question_category`;
        ');
    }
}

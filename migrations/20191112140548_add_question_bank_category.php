<?php

use Phpmig\Migration\Migration;

class AddQuestionBankCategory extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `question_bank_category` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(64) NOT NULL COMMENT '分类名称',
              `bankNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题库数量',
              `depth` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '当前层级',
              `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级分类id',
              `orgId` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '组织机构id',
              `orgCode` varchar(265) NOT NULL DEFAULT '1.' COMMENT '组织机构编码',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库分类表';
        ");

        $currentTime = time();
        $connection->exec("
            INSERT INTO `question_bank_category` (
                `name`,
                `createdTime`,
                `updatedTime`
            ) VALUES (
                '默认分类',
                '{$currentTime}',
                '{$currentTime}'
            )
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
            DROP TABLE IF EXISTS `question_bank_category`;
        ');

        $connection->exec("
            delete from `question_bank_category` where name = '默认分类';
        ");
    }
}

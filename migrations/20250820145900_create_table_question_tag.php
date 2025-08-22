<?php

use Phpmig\Migration\Migration;

class CreateTableQuestionTag extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `question_tag_group` (
              `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` VARCHAR(128) NOT NULL COMMENT '名称',
              `seq` INT(10) unsigned NOT NULL COMMENT '序号',
              `tagNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签数量',
              `status` TINYINT(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态(0: 禁用 1: 启用)',
              `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
              `updatedTime` INT(10) unsigned NOT NULL DEFAULT 0,
              KEY `name` (`name`),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目标签组表';

            CREATE TABLE IF NOT EXISTS `question_tag` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `groupId` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签组ID',
                `name` VARCHAR(128) NOT NULL COMMENT '名称',
                `seq` INT(10) unsigned NOT NULL COMMENT '序号',
                `status` TINYINT(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态(0: 禁用 1: 启用)',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT 0,
                KEY `groupId` (`groupId`),
                KEY `name` (`name`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目标签表';

            CREATE TABLE IF NOT EXISTS `question_tag_relation` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `itemId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '题目ID',
                `tagId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签ID',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
                KEY `itemId` (`itemId`),
                KEY `tagId` (`tagId`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `question_tag_group`;
            DROP TABLE IF EXISTS `question_tag`;
            DROP TABLE IF EXISTS `question_tag_relation`;
        ');
    }
}

<?php

use Phpmig\Migration\Migration;

class SearchKeyword extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `search_keyword`(
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(64) NOT NULL COMMENT '关键字名称',
              `type` varchar(64) NOT NULL COMMENT '关键字类型',
              `times` int(10) NOT NULL DEFAULT 1 COMMENT '被搜索次数',
              `createdTime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE INDEX `name` (`name`, `type`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `search_keyword`;');
    }
}

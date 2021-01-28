<?php

use Phpmig\Migration\Migration;

class App extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "
            CREATE TABLE `app` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
              `name` varchar(255) NOT NULL COMMENT '应用名称',
              `code` varchar(16) NOT NULL COMMENT '应用编码',
              `type` enum('plugin','theme') NOT NULL DEFAULT 'plugin' COMMENT '应用类型(plugin插件应用, theme主题应用)',
              `description` varchar(1024) NOT NULL DEFAULT ''  COMMENT '应用描述',
              `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
              `version` varchar(16) NOT NULL COMMENT '应用当前版本',
              `author` varchar(255) NOT NULL DEFAULT '' COMMENT '应用开发者名称',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用安装时间',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已安装的应用';
        ";

        $container = $this->getContainer();
        $container['db']->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $container['db']->exec('DROP TABLE `app`');
    }
}

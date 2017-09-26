<?php

use Phpmig\Migration\Migration;

class BizSessionAndOnline extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_session` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `sess_id` varbinary(128) NOT NULL,
              `sess_user_id` int(10) unsigned NOT NULL DEFAULT '0',
              `sess_data` blob NOT NULL,
              `sess_time` int(10) unsigned NOT NULL,
              `created_time` int(10) unsigned NOT NULL,
              `sess_lifetime` mediumint(9) NOT NULL,
              `source` VARCHAR(32) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        ");

        $connection->exec("
            CREATE TABLE `biz_online` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
              `sess_id` varchar(128) NOT NULL DEFAULT '',
              `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户的id, 0代表游客',
              `lifetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生命周期',
              `access_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后访问时间',
              `access_url` VARCHAR(1024) NOT NULL DEFAULT '',
              `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '客户端ip',
              `user_agent` varchar(1024) NOT NULL DEFAULT '',
              `device` varchar(1024) COMMENT '设备',
              `os` text COMMENT '操作系统',
              `client` text COMMENT '客户端信息',
              `device_brand` varchar(1024) COMMENT '品牌名称',
              `source` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '当前在线用户的来源，例如：app,web,mobile',
              `created_time` int(10) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}

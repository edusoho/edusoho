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
            CREATE TABLE IF NOT EXISTS `biz_session` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
              `sess_id` varbinary(128) NOT NULL,
              `sess_data` blob NOT NULL,
              `sess_time` int(10) unsigned NOT NULL,
              `sess_deadline` int(10) unsigned NOT NULL,
              `created_time` int(10) unsigned NOT NULL ,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sess_id` (`sess_id`),
              INDEX sess_deadline (`sess_deadline`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_online` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
              `sess_id` varbinary(128) NOT NULL,
              `active_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后活跃时间',
              `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '离线时间',
              `is_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
              `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户的id, 0代表游客',
              `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '客户端ip',
              `user_agent` varchar(1024) NOT NULL DEFAULT '',
              `source` VARCHAR(32) NOT NULL DEFAULT 'unknown' COMMENT '当前在线用户的来源，例如：app, pc, mobile',
              `created_time` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              INDEX deadline (`deadline`),
              INDEX is_login (`is_login`),
              INDEX active_time (`active_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("drop table `biz_session`");
        $connection->exec("drop table `biz_online`");
    }
}

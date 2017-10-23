<?php

use Phpmig\Migration\Migration;

class BizToken extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_token` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `place` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '使用场景',
              `_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'KEY',
              `data` text COLLATE utf8_unicode_ci NOT NULL COMMENT '数据',
              `expired_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
              `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最多可被校验的次数',
              `remaining_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余可被校验的次数',
              `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `_key` (`_key`),
              KEY `expired_time` (`expired_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE `biz_token`");
    }
}

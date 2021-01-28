<?php

use Phpmig\Migration\Migration;

class BizTargetlog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_targetlog` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `target_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象类型',
              `target_id` varchar(48) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象ID',
              `action` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志行为',
              `level` smallint(6) NOT NULL DEFAULT '0' COMMENT '日志等级',
              `message` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志信息',
              `context` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志上下文',
              `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
              `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人IP',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `idx_target` (`target_type`(8),`target_id`(8)),
              KEY `idx_level` (`level`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE `biz_targetlog`");
    }
}

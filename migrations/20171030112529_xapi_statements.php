<?php

use Phpmig\Migration\Migration;

class XapiStatements extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
          CREATE TABLE `xapi_statement` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `uuid` varchar(64) NOT NULL,
          `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
          `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
          `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
          `verb` varchar(32) NOT NULL DEFAULT '' COMMENT '用户行为',
          `target_id` int(10) DEFAULT NULL COMMENT '目标Id',
          `target_type` varchar(32) NOT NULL COMMENT '目标类型',
          `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
          `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
          PRIMARY KEY (`id`),
          UNIQUE KEY `uuid` (`uuid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE `xapi_statement`');
    }
}

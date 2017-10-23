<?php

use Phpmig\Migration\Migration;

class BizXapi extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_xapi_statement` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `uuid` varchar(64)  NOT NULL ,
              `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
              `data` text NOT NULL COMMENT '数据',
              `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
              `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
              `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
              `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uuid` (`uuid`),
              KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE `biz_xapi_statement`');
    }
}

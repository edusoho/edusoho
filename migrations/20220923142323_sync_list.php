<?php

use Phpmig\Migration\Migration;

class SyncList extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `sync_list` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `type` varchar(32) NOT NULL COMMENT '类型',
            `status` varchar(16) NOT NULL DEFAULT 'new' COMMENT '消息状态',
            `data` varchar(300) NOT NULL COMMENT '需要更新数据信息',
            `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}

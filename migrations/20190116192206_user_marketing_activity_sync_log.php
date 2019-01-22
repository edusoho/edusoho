<?php

use Phpmig\Migration\Migration;

class UserMarketingActivitySyncLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `user_marketing_activity_sync_log` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `args` varchar(255) NOT NULL DEFAULT '0',
                `data` text COMMENT '同步的数据',
                `target` varchar(32) NOT NULL DEFAULT '' COMMENT '同步对象 all全部 mobile手机号',
                `targetValue` varchar(50) DEFAULT '0' COMMENT '同步对象值',
                `rangeStartTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '同步范围开始时间',
                `rangeEndTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '同步范围结束时间',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='同步日志表';   
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('
            DROP TABLE IF EXISTS `user_marketing_activity_sync_log`;
        ');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}

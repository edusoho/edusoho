<?php

use Phpmig\Migration\Migration;

class AntiFraudRemind extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE IF NOT EXISTS `anti_fraud_remind` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `userId` int(10) unsigned DEFAULT '0' COMMENT '用户id',
            `lastRemindTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次提醒时间',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `anti_fraud_remind`');
    }
}

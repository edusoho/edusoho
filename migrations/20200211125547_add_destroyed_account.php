<?php

use Phpmig\Migration\Migration;

class AddDestroyedAccount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `destroyed_account` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `recordId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注销记录ID',
              `userId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已注销用户ID',
              `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '注销用户曾用名',
              `time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注销时间',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='已注销帐号表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            DROP TABLE IF EXISTS `destroyed_account`;
        ');
    }
}

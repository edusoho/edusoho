<?php

use Phpmig\Migration\Migration;

class UserActiveLog extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
           CREATE TABLE `user_active_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `userId` int(11) NOT NULL COMMENT '用户Id',
              `activeTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `createdTime` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活跃用户记录表';
            INSERT INTO user_active_log (userid, activeTime,createdTime) SELECT `sess_user_id`, FROM_UNIXTIME(`sess_time`, '%Y%m%d'),`sess_time` FROM `sessions`;
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `user_active_log`;');
    }
}

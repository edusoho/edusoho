<?php

use Phpmig\Migration\Migration;

class BehaviorVerificationBlackIp extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `behavior_verification_black_ip` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `ip` varchar(32) NOT NULL COMMENT 'ip',
            `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
            `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;);
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}

<?php

use Phpmig\Migration\Migration;

class CreateTableSmsRequestLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `sms_request_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `fingerprint` varchar(32) NOT NULL COMMENT '行为指纹',
            `coordinate` varchar(32) NOT NULL COMMENT '坐标',
            `ip` varchar(32) NOT NULL COMMENT 'ip',
            `mobile` varchar(11)  NOT NULL COMMENT '手机号',
            `userAgent` varchar(255) NOT NULL DEFAULT '',
            `isIllegal`  tinyint(1)  NOT NULL COMMENT '是非法记录',
            `disableType`  varchar(16)  NOT NULL default 'none' COMMENT '禁用类型',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
            PRIMARY KEY (`id`),
            KEY `mobile` (mobile),
            KEY `ip` (ip),
            KEY `createdTime` (createdTime)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='短信请求日志表';
            ALTER TABLE `behavior_verification_black_ip` rename to `sms_black_list`;
            DROP TABLE `behavior_verification_coordinate`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DROP TABLE `sms_request_log`;
            ALTER TABLE `sms_black_list` rename to `behavior_verification_black_ip`;
            CREATE TABLE `behavior_verification_coordinate` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `coordinate` varchar(16) NOT NULL COMMENT '坐标',
            `hit_counts` bigint NOT NULL default 0 COMMENT '命中次数',
            `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
            `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }
}

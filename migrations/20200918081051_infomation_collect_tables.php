<?php

use Phpmig\Migration\Migration;

class InfomationCollectTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `infomation_collect_event` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(256) NOT NULL COMMENT '信息采集的标题',
              `action` varchar(32) NOT NULL COMMENT '信息采集的位置行为',
              `formTitle` varchar(64) NOT NULL COMMENT '信息采集表单的标题',
              `status` varchar(32) NOT NULL DEFAULT 'open' COMMENT '信息采集开启状态',
              `allowSkip` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许跳过',
              `creator` int(11) unsigned NOT NULL COMMENT '创建者',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集事件表';
        ");

        $biz['db']->exec("
            CREATE TABLE `infomation_collect_item` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
              `code` varchar(32) NOT NULL COMMENT '表单的code，作为表单的name',
              `labelName` varchar(32) NOT NULL COMMENT '表单的标签名',
              `seq` int(10) unsigned NOT NULL COMMENT '表单位置顺序',
              `required` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否必填',
              `createdTime` int(10) unsigned DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='采集事件表单项';
        ");

        $biz['db']->exec("
            CREATE TABLE `infomation_collect_location` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
              `action` varchar(32) NOT NULL DEFAULT '' COMMENT '信息采集的位置行为',
              `targetType` varchar(32) NOT NULL COMMENT '目标类型，比如course,classroom,none',
              `targetId` int(11) DEFAULT NULL COMMENT '目标ID 0为当前类型全部',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `uk_action_type_targetid` (`action`,`targetType`,`targetId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集位置';
        ");

        $biz['db']->exec("
            CREATE TABLE `infomation_collect_result` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `formTitle` varchar(64) NOT NULL COMMENT '表单标题',
              `submitter` int(11) unsigned NOT NULL COMMENT '提交人',
              `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
              `createdTime` int(10) unsigned NOT NULL,
              `updatedTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据采集结果';
        ");

        $biz['db']->exec("
            CREATE TABLE `infomation_collect_result_item` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `eventId` int(11) unsigned NOT NULL COMMENT '采集事件ID',
              `resultId` int(11) unsigned NOT NULL COMMENT '采集结果ID',
              `code` varchar(32) NOT NULL COMMENT '表单的code，作为表单的name',
              `labelName` varchar(32) NOT NULL COMMENT '表单的标签名',
              `value` varchar(4096) NOT NULL DEFAULT '' COMMENT '表单值',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息采集表单值';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `infomation_collect_event`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `infomation_collect_item`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `infomation_collect_location`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `infomation_collect_result `;');
        $biz['db']->exec('DROP TABLE IF EXISTS `infomation_collect_result_item`;');
    }
}

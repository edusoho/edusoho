<?php

use Phpmig\Migration\Migration;

class ReportAuditTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("CREATE TABLE `report_audit` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `module` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标模块',
          `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标类型',
          `targetId` int(11) NOT NULL COMMENT '举报目标id',
          `content` mediumtext NOT NULL COMMENT '举报正文',
          `author` int(11) NOT NULL COMMENT '作者',
          `reportCount` int(11) NOT NULL DEFAULT '0' COMMENT '被举报次数',
          `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
          `auditor` int(11) DEFAULT '0' COMMENT '审核人',
          `status` varchar(32) NOT NULL DEFAULT '' COMMENT '审核状态',
          `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
          `createdTime` int(11) DEFAULT NULL,
          `updatedTime` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $biz['db']->exec("CREATE TABLE `report_audit_record` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `auditId` int(11) unsigned NOT NULL COMMENT '审核表ID',
          `content` mediumtext NOT NULL COMMENT '审核正文',
          `author` int(11) NOT NULL COMMENT '作者',
          `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
          `auditor` int(11) NOT NULL COMMENT '审核者',
          `status` varchar(32) NOT NULL DEFAULT '' COMMENT '审核状态',
          `originStatus` varchar(32) NOT NULL DEFAULT '' COMMENT '原审核状态',
          `auditTime` int(11) unsigned NOT NULL COMMENT '审核时间',
          `createdTime` int(11) unsigned NOT NULL,
          `updatedTime` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $biz['db']->exec("CREATE TABLE `report_record` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `auditId` int(11) unsigned NOT NULL COMMENT '审核ID',
          `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '举报目标类型',
          `targetId` int(11) NOT NULL COMMENT '举报目标id',
          `reporter` int(11) unsigned NOT NULL COMMENT '举报者',
          `content` mediumtext NOT NULL COMMENT '举报正文',
          `author` int(11) NOT NULL COMMENT '作者',
          `reportTags` varchar(1024) NOT NULL DEFAULT '' COMMENT '举报标签',
          `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `report_audit`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `report_audit_record`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `report_record`;');
    }
}

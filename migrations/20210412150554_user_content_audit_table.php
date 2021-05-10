<?php

use Phpmig\Migration\Migration;

class UserContentAuditTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `user_content_audit` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `targetType` varchar(32) NOT NULL DEFAULT '' COMMENT '内容类型',
              `targetId` int(10) NOT NULL COMMENT '内容ID',
              `author` int(11) NOT NULL COMMENT '作者ID',
              `content` mediumtext COMMENT '内容',
              `sensitiveWords` varchar(2048) DEFAULT '' COMMENT '敏感词',
              `auditor` int(11) DEFAULT NULL COMMENT '最后一次审核人',
              `status` varchar(32) NOT NULL DEFAULT '' COMMENT '当前审核状态',
              `auditTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后审核时间',
              `createdTime` int(11) unsigned NOT NULL,
              `updatedTime` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $biz['db']->exec("
        CREATE TABLE `user_content_audit_record` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `auditId` int(11) unsigned NOT NULL COMMENT '审核表ID',
              `author` int(11) NOT NULL COMMENT '作者',
              `content` mediumtext NOT NULL COMMENT '审核内容',
              `sensitiveWords` varchar(2048) NOT NULL DEFAULT '' COMMENT '敏感词',
              `auditor` int(11) NOT NULL COMMENT '审核人',
              `status` varchar(32) NOT NULL DEFAULT '' COMMENT '状态',
              `originStatus` varchar(32) NOT NULL DEFAULT '' COMMENT '原审核状态',
              `auditTime` int(11) unsigned NOT NULL COMMENT '审核时间',
              `createdTime` int(11) unsigned NOT NULL,
              `updatedTime` int(11) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `user_content_audit`;');
        $biz['db']->exec('DROP TABLE IF EXISTS `user_content_audit_record`;');
    }
}

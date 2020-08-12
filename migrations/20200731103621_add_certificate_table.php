<?php

use Phpmig\Migration\Migration;

class AddCertificateTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `certificate_template` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '模板名称',
                `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                `basemap` VARCHAR (255) DEFAULT '' COMMENT '底图',
                `stamp` VARCHAR (255) DEFAULT '' COMMENT '印章',
                `styleType`  VARCHAR (32) DEFAULT 'horizontal' COMMENT '样式类型,horizontal横版,vertical竖版',
                `certificateName` VARCHAR (255) DEFAULT '' COMMENT '证书标题',
                `recipientContent` VARCHAR (255) DEFAULT '' COMMENT '被授予人信息',
                `certificateContent` text COMMENT '证书正文',
                `qrCodeSet` tinyint(1) unsigned DEFAULT 1 COMMENT '二维码设置',
                `createdUserId` INT(10) unsigned DEFAULT '0' COMMENT '创建者Id',
                `dropped` tinyint(1) unsigned DEFAULT 0 COMMENT '是否废弃',
                `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书模板表';
            
            CREATE TABLE `certificate` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '证书名称',
                `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                `targetId` int(10) DEFAULT '0' COMMENT '发放对象ID',
                `description` text COMMENT '证书描述',
                `templateId` int(10) DEFAULT '0' COMMENT '底图',
                `code` VARCHAR (255) DEFAULT '' COMMENT '证书编码',
                `status` varchar(64) DEFAULT 'draft' COMMENT '证书状态',
                `expiryDay` int(10) DEFAULT '0' COMMENT '有效期天数，0表示长期有效',
                `autoIssue` tinyint(1) unsigned DEFAULT '1' COMMENT '是否自动发放',
                `createdUserId` INT(10) unsigned DEFAULT '0' COMMENT '创建者Id',
                `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书表';
            
            CREATE TABLE `certificate_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) COMMENT '用户ID',
                `certificateId` int(10) COMMENT '证书ID',
                `certificateCode` VARCHAR (255) DEFAULT '' COMMENT '证书编码',
                `targetType` varchar(64) DEFAULT '' COMMENT '发放类型',
                `targetId` int(10) DEFAULT '0' COMMENT '发放对象ID',
                `status` varchar(64) DEFAULT 'none' COMMENT '证书审核状态',
                `rejectReason` VARCHAR (255) DEFAULT '' COMMENT '拒绝原因',
                `auditUserId` INT(10) unsigned DEFAULT '0' COMMENT '审核用户ID',
                `auditTime` INT(10) unsigned DEFAULT '0' COMMENT '审核时间',
                `expiryTime` int(10) DEFAULT '0' COMMENT '过期时间',
                `issueTime` int(10) DEFAULT '0' COMMENT '发放时间',
                `certificateImg` VARCHAR (255) DEFAULT '' COMMENT '证书图片',
                `createdTime` INT(10) unsigned DEFAULT '0' COMMENT '创建时间',
                `updatedTime` INT(10) unsigned DEFAULT '0'  COMMENT '更新时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证书记录表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `certificate_template`');
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `certificate`');
    }
}

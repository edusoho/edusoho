<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160415021324 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `upload_file_inits` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `globalId` varchar(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
              `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
              `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
              `targetId` int(11) NOT NULL COMMENT '所存目标id',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
              `filename` varchar(1024) NOT NULL DEFAULT '',
              `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
              `fileSize` bigint(20) NOT NULL DEFAULT '0',
              `etag` VARCHAR( 256 ) NOT NULL DEFAULT  '',
              `length` INT UNSIGNED NOT NULL DEFAULT  '0',
              `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
              `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
              `metas` text,
              `metas2` TEXT NULL DEFAULT NULL,
              `type` ENUM(  'document',  'video',  'audio',  'image',  'ppt',  'flash', 'other' ) NOT NULL DEFAULT 'other',
              `storage` enum('local','cloud') NOT NULL,
              `convertParams` TEXT NULL COMMENT  '文件转换参数',
              `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
              `updatedTime` int(10) unsigned DEFAULT '0',
              `createdUserId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `hashId` (`hashId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

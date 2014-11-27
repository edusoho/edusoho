<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141028195545 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql("ALTER TABLE upload_files MODIFY targetId INT(11);");
    	$this->addSql("ALTER TABLE upload_files CHANGE targetType targetType VARCHAR(64) NULL");
        $this->addSql("ALTER TABLE upload_files ADD linkCount int(10) unsigned NOT NULL DEFAULT 0 AFTER `canDownload`;");
		$this->addSql("CREATE TABLE `course_file` (
                                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                `fileId` int(10) unsigned NOT NULL COMMENT '文件ID',
                                                `targetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                                                `targetType` enum('courselesson','coursematerial') NOT NULL DEFAULT 'coursematerial' COMMENT '资料类型（课时文件，教师备用资料文件）',
         					`userId` int(10) unsigned NOT NULL COMMENT '创建文件引用的用户ID',
                                                `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                                                PRIMARY KEY (`id`),
                                                KEY `fileId` (`fileId`),
                                                KEY `targetId` (`targetId`),
                                                KEY `userId` (`userId`),
                                                KEY `createdTime` (`createdTime`)
                                            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		$this->addSql ( "CREATE TABLE `upload_files_share` (
						`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						`sourceUserId` int(10) unsigned NOT NULL COMMENT '上传文件的用户ID',
						`targetUserId` int(10) unsigned NOT NULL COMMENT '文件分享目标用户ID',
						`isActive` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
						`createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
						`updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
						PRIMARY KEY (`id`),
						KEY `sourceUserId` (`sourceUserId`),
						KEY `targetUserId` (`targetUserId`),
						KEY `createdTime` (`createdTime`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
			    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

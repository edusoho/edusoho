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
        $this->addSql("ALTER TABLE upload_files ADD usedCount int(10) unsigned NOT NULL DEFAULT 0 AFTER `canDownload`;");
        $this->addSql("UPDATE upload_files files, (SELECT count(*) AS co,mediaId FROM course_lesson WHERE type IN ('video','audio','ppt') GROUP BY mediaId) filesUsedCount SET files.usedCount = files.usedCount+filesUsedCount.co WHERE files.id=filesUsedCount.mediaId;");
        $this->addSql("UPDATE upload_files files,(SELECT count(*) AS co,fileId FROM course_material GROUP BY fileId) filesUsedCount SET files.usedCount = files.usedCount+filesUsedCount.co WHERE files.id=filesUsedCount.fileId;");

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

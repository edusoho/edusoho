<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161108141257 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `upload_files` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        $this->addSql("ALTER TABLE `upload_file_inits` CHANGE COLUMN `type` `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        $this->addSql("CREATE TABLE `subtitle` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '字幕名称',
                `subtitleId` int(10) UNSIGNED NOT NULL COMMENT 'subtitle的uploadFileId',
                `mediaId` int(10) UNSIGNED NOT NULL COMMENT 'video/audio的uploadFileId',
                `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) COMMENT='字幕关联表';"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

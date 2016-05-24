<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150703170020 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `upload_files` ADD `globalId` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '云文件ID' AFTER `id`;");
        $this->addSql("ALTER TABLE `upload_files` ADD `status` ENUM('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态' AFTER `length`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

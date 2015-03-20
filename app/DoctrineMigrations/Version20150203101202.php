<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150203101202 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `upload_files` CHANGE `type` `type` ENUM('document','video','audio','image','ppt','other','flash') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'other' COMMENT '文件类型';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

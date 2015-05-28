<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150424171555 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `block` ADD `meta` TEXT NULL DEFAULT NULL COMMENT '编辑区元信息' AFTER `code`;");
        $this->addSql("ALTER TABLE `block` ADD `data` TEXT NULL DEFAULT NULL COMMENT '编辑区内容' AFTER `meta`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151210103233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql(" ALTER TABLE `article` ADD `attachmentIds` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `tagIds`;");
        $this->addSql(" ALTER TABLE `file` ADD `fileName` VARCHAR(1024) NOT NULL AFTER `uri`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

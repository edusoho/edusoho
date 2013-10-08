<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130604013354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("ALTER TABLE  `category_group` ADD  `code` VARCHAR( 64 ) NOT NULL AFTER  `id`");
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

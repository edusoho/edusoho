<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130528145536 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("ALTER TABLE  `course` ADD  `subtitle` VARCHAR( 1024 ) NOT NULL COMMENT  '副标题' AFTER  `title` ;");
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

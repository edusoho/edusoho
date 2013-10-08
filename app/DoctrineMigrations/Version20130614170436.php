<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130614170436 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE  `course_chapter` ADD  `seq` INT UNSIGNED NOT NULL AFTER  `number`;");
    	$this->addSql("ALTER TABLE  `course_lesson` ADD  `seq` INT UNSIGNED NOT NULL AFTER  `number`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130614142708 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE  `course` CHANGE  `startTime`  `startTime` INT( 10 ) UNSIGNED NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE  `course` CHANGE  `endTime`  `endTime` INT( 10 ) UNSIGNED NULL DEFAULT NULL;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130606151344 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
    ALTER TABLE  `course` CHANGE  `SCG`  `audiences` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'audiences';
        ");
    }

    public function down(Schema $schema)
    {

    }
}

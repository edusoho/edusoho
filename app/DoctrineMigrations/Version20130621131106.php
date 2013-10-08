<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130621131106 extends AbstractMigration
{
    public function up(Schema $schema)
    {
       $this->addSql("
ALTER TABLE  `course_member` ADD  `isVisible` TINYINT( 2 ) NOT NULL DEFAULT  '1' COMMENT  '可见与否，默认为可见' AFTER  `userId`;
       ");

    }

    public function down(Schema $schema)
    {
        
    }
}

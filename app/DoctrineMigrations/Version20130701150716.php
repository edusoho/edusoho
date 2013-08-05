<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130701150716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
       $this->addSql("ALTER TABLE  `course_favorite` ADD  `createdTime` INT( 10 ) NOT NULL COMMENT  '创建时间' AFTER  `userId`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

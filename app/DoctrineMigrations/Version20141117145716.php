<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141117145716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `courseware` ADD `categoryId` INT(10) NOT NULL AFTER `relatedKnowledgeIds`, ADD INDEX (`categoryId`) ;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

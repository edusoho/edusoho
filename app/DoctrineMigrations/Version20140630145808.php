<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140630145808 extends AbstractMigration
{
    public function up(Schema $schema)
    {	
    	$this->addSql("ALTER TABLE `homework_result` ADD `commitStatus` ENUM('committed','uncommitted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'uncommitted' AFTER `status`;");
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

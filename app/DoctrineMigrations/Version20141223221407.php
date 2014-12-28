<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141223221407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `cash_flow` ADD `cash` FLOAT(10,2) NOT NULL DEFAULT '0' AFTER `cashType`;");
    	$this->addSql("ALTER TABLE `cash_flow` ADD `parentSn` bigint(20) NULL AFTER `cashType`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

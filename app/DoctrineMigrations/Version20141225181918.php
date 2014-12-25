<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141225181918 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
    		ALTER TABLE `vip_level` ADD `monthCoinPrice` FLOAT(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `yearPrice`;	
    	");

    	$this->addSql("	
			ALTER TABLE `vip_level` ADD `yearCoinPrice` FLOAT(10,2) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `monthCoinPrice`;
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

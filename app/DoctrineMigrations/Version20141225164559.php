<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141225164559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("	
			ALTER TABLE `vip_history` ADD `priceType` ENUM('RMB','Coin') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'RMB' ;
    	");      
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141216171811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `orders` ADD `totalPrice` FLOAT(10,2) NOT NULL AFTER `amount`;");
    	$this->addSql("ALTER TABLE `orders` ADD `coinRate` FLOAT(10,2)  AFTER `coinAmount`;");
    	$this->addSql("ALTER TABLE `orders` ADD `priceType` enum('RMB','Coin') NOT NULL AFTER `coinRate`;");
    	
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

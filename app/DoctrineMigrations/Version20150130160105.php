<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150130160105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `orders` CHANGE `totalPrice` `totalPrice` FLOAT(10,2) NOT NULL DEFAULT '0';");
        $this->addSql("ALTER TABLE `orders` CHANGE `coinAmount` `coinAmount` FLOAT(10,2) NOT NULL DEFAULT '0';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

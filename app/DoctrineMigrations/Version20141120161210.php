<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141120161210 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `course` ADD `coinPrice` FLOAT(10,2) NOT NULL DEFAULT 0.00 AFTER `price`;");
    	$this->addSql("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

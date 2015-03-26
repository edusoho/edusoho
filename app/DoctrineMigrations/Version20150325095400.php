<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150325095400 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `vip_level` ADD `courseDiscount` INT NOT NULL AFTER `createdTime`");
        $this->addSql("ALTER TABLE `vip_level` ADD `signInCards` INT NOT NULL AFTER `courseDiscount`");
        $this->addSql("ALTER TABLE `cash_flow` ADD `cashType` enum('RMB','Coin') NOT NULL DEFAULT 'Coin'");
        $this->addSql("ALTER TABLE `cash_flow` ADD `cash` float(10,2) NOT NULL DEFAULT '0.00'");
        $this->addSql("ALTER TABLE `cash_flow` ADD `parentSn` bigint(20) DEFAULT NULL'");

 
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

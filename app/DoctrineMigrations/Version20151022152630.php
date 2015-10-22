<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151022152630 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $this->addSql("ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $this->addSql("ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

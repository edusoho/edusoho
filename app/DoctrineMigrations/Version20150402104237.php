<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150402104237 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `orders` ADD  `discountId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '折扣活动ID';");
        $this->addSql("ALTER TABLE  `orders` ADD  `discount` FLOAT( 10, 2 ) NOT NULL DEFAULT  '10' COMMENT  '折扣' AFTER  `discountId`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

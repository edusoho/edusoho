<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160112171411 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `keyword` ADD `state` ENUM('','replaced','banned') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`;");
        $this->addSql("ALTER TABLE `keyword_banlog` ADD `state` ENUM('banned','replaced') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `keywordName`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

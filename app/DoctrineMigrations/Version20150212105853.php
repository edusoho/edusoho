<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150212105853 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `thread` CHANGE  `isStick`  `sticky` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '置顶';");
        $this->addSql("ALTER TABLE  `thread` CHANGE  `isElite`  `nice` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '加精'");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

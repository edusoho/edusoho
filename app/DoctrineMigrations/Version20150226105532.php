<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150226105532 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
            $this->addSql(
            "ALTER TABLE `classroom` ADD `rating` FLOAT UNSIGNED NOT NULL DEFAULT '0' COMMENT '排行数值' AFTER `postNum`, ADD `ratingNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票人数' AFTER `rating`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

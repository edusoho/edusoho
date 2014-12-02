<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141202104230 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `groups_thread` ADD `rewardCoin` INT(10) UNSIGNED NOT NULL DEFAULT '0' ;");
        $this->addSql("ALTER TABLE `groups_thread` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'default' AFTER `rewardCoin`;");
        $this->addSql("ALTER TABLE `groups_thread_post` ADD `adopt` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `createdTime`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

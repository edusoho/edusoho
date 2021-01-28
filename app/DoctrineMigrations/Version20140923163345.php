<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140923163345 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `groups_thread` CHANGE `lastPostMemberId` `lastPostMemberId` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `groups_thread` CHANGE `lastPostTime` `lastPostTime` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `groups_thread` CHANGE `hitNum` `hitNum` INT(10) UNSIGNED NOT NULL DEFAULT '0';
            ALTER TABLE `groups_thread_post` CHANGE `fromUserId` `fromUserId` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

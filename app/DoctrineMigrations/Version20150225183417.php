<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150225183417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `thread` CHANGE  `lastPostMemberId`  `lastPostUserId` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '最后回复人ID'");
        $this->addSql("ALTER TABLE `thread_post` DROP `fromUserId`");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

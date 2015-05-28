<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150304085847 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `thread` ADD  `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人' AFTER  `content`");
        $this->addSql("ALTER TABLE  `thread_post` ADD  `ats` TEXT NULL DEFAULT NULL COMMENT  '@(提)到的人' AFTER  `content`");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140801145736 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `user_token` ADD  `times` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'TOKEN的校验次数限制(0表示不限制)' AFTER  `data`");
        $this->addSql("ALTER TABLE  `user_token` ADD  `remainedTimes` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'TOKE剩余校验次数' AFTER  `times`");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

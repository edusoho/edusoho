<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150522124301 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `article` ADD `postNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复数' AFTER `sticky`;");
        $this->addSql("ALTER TABLE `article` ADD `upsNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数' AFTER `postNum`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

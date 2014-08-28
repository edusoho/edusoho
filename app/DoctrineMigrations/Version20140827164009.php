<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140827164009 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `course` ADD `compulsory` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否必修' AFTER `term`;");
        $this->addSql("ALTER TABLE `course` ADD `public` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否公共课' AFTER `compulsory`;");
        $this->addSql("ALTER TABLE `course` ADD `templateId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模板ID' AFTER `public`;");   
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

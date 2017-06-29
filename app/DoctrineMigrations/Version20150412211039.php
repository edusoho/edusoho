<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150412211039 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course` ADD `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id' AFTER `locationId`;");
        $this->addSql("ALTER TABLE `thread` CHANGE `maxUsers` `maxUsers` INT(10) NOT NULL DEFAULT '0' COMMENT '最大人数';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

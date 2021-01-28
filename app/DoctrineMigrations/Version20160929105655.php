<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160929105655 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `im_conversation` ADD `targetType` VARCHAR(16) NOT NULL DEFAULT '' AFTER `no`");
        $this->addSql("ALTER TABLE `im_conversation` ADD `targetId` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`");
        $this->addSql("ALTER TABLE `im_conversation` ADD `title` VARCHAR(255) NOT NULL DEFAULT ''");

        $this->addSql("alter table `course` drop column convNo");
        $this->addSql("alter table `classroom` drop column convNo");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141028095644 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_lesson_view` CHANGE `fileStorage` `fileStorage` ENUM('local','cloud','net','none') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        $this->addSql("ALTER TABLE `course_lesson_view` CHANGE `fileType` `fileType` ENUM('document','video','audio','image','ppt','other','none') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none';");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

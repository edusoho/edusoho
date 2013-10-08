<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130618231542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `course_lesson` CHANGE  `summary`  `summary` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $this->addSql("ALTER TABLE  `course_lesson` CHANGE  `tags`  `tags` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $this->addSql("ALTER TABLE  `course_lesson` CHANGE  `content`  `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $this->addSql("ALTER TABLE  `course_lesson` CHANGE  `media`  `media` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

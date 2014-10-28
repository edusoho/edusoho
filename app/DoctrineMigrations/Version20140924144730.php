<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140924144730 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_lesson_learn` ADD `watchTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `learnTime`, ADD `videoStatus` ENUM('paused','playing') NOT NULL DEFAULT 'paused' AFTER `watchTime`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

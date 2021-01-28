<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150515160712 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_lesson` ADD `homeworkId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '作业iD' AFTER `mediaUri`;");
        $this->addSql("ALTER TABLE `course_lesson` ADD `exerciseId` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT '练习ID' AFTER `homeworkId`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

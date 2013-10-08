<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130529135538 extends AbstractMigration
{
    public function up(Schema $schema)
    {

        $this->addSql("
            ALTER TABLE  `course_review` ADD  `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '评论title' AFTER  `courseId`
            ");
        $this->addSql("
            ALTER TABLE  `course_review` ADD  `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '评论内容' AFTER  `title`
            ");
        $this->addSql("ALTER TABLE `course_review` DROP `score`;");
        $this->addSql("ALTER TABLE `course` DROP `score`;");
        $this->addSql("ALTER TABLE  `course` CHANGE  `voteNum`  `ratingNum` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '投票人数';");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

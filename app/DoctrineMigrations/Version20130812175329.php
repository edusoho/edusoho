<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130812175329 extends AbstractMigration
{
    public function up(Schema $schema)
    {
         $this->addSql("RENAME TABLE  `lesson_quiz` TO  `course_quiz` ;");
         $this->addSql("RENAME TABLE  `lesson_quiz_item` TO  `course_quiz_item` ;");
         $this->addSql("RENAME TABLE  `lesson_quiz_item_answer` TO  `course_quiz_item_answer` ;");
    }

    public function down(Schema $schema)
    {

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130711094748 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(" DROP TABLE course_quiz;");
        $this->addSql(" DROP TABLE course_quiz_answer;");
        $this->addSql(" DROP TABLE course_quiz_item;");
        $this->addSql(" DROP TABLE course_quiz_used;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

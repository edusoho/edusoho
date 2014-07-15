<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140708220626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `course_lesson` ADD  `giveCredit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '学完课时获得的学分' AFTER  `content`;");
        $this->addSql("ALTER TABLE  `course_lesson` ADD  `requireCredit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '学习课时前，需达到的学分' AFTER  `giveCredit`;");
        $this->addSql("ALTER TABLE  `course` ADD  `giveCredit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '学完课程所有课时，可获得的总学分' AFTER  `lessonNum`;");
        $this->addSql("ALTER TABLE  `course_member` ADD  `credit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '学员已获得的学分' AFTER  `learnedNum`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

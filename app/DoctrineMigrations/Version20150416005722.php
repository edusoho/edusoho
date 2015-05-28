<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150416005722 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `course` ADD  `watchLimit` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '课时观看次数限制' AFTER `daysOfNotifyBeforeDeadline`;");
        $this->addSql("ALTER TABLE  `course_lesson_learn` ADD  `watchNum` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '课时已观看次数' AFTER  `watchTime`");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

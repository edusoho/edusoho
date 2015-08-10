<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150513181253 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_lesson` ADD COLUMN `suggestHours` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '建议学习时长'");
        $this->addSql("UPDATE `course_lesson` SET `suggestHours` = CEIL(length/3600) WHERE type IN('video','audio') AND length is not Null'");
        $this->addSql("UPDATE `course_lesson` SET `suggestHours` = 1 WHERE type IN('video','audio')  AND  length is Null");
        $this->addSql("UPDATE course_lesson SET suggestHours=2  WHERE type NOT IN('video','audio') AND  length is Null");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

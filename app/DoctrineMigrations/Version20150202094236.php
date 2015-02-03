<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150202094236 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("ALTER TABLE `classroom` CHANGE `teacherId` `headerTeacherId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班主任ID';");

        $this->addSql("ALTER TABLE `classroom` ADD `teacherIds` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '教师IDs'  AFTER `headerTeacherId`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

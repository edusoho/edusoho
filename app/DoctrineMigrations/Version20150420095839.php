<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150420095839 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `status` ADD `courseId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程Id' AFTER `userId`;");
        $this->addSql("ALTER TABLE `status` ADD `classroomId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '班级id' AFTER `courseId`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150412211039 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course` ADD `parentId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程的父Id' AFTER `discountActivityId`;");
        $this->addSql("ALTER TABLE `classroom_courses` ADD `disabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用' AFTER `courseId`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

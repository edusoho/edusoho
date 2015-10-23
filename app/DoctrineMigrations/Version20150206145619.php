<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150206145619 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `course_member` ADD `classroomId` INT(10) NOT NULL DEFAULT '0'  COMMENT '班级ID' AFTER `courseId`; ");
    	$this->addSql("ALTER TABLE `course_member` ADD `joinedType` ENUM('course','classroom') NOT NULL DEFAULT 'course' COMMENT '购买班级或者课程加入学习' AFTER `classroomId`; ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

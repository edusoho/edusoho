<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141208150051 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("CREATE TABLE `course_subcourse` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
            `courseId` int(10) unsigned NOT NULL COMMENT '课程包id',
            `subcourseId` int(10) unsigned NOT NULL COMMENT '子课程id',
            `sequence` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '子课程顺序',
            PRIMARY KEY (`id`),
            UNIQUE KEY `courseId_subcourseId` (`courseId`,`subcourseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程包课程从属关系表';
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

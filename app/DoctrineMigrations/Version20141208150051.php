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
    	$this->addSql("CREATE TABLE `course_package_item` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
			`packageId` int(10) unsigned NOT NULL COMMENT '课程包id',
			`courseId` int(10) unsigned NOT NULL COMMENT '课程id',
			PRIMARY KEY (`id`),
			UNIQUE KEY `packageId_courseId_unique` (`packageId`,`courseId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程包与课程关系表';
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140802111806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
				CREATE TABLE IF NOT EXISTS `course_lesson_view` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`courseId` int(10) NOT NULL,
				`lessonId` int(10) NOT NULL,
				`fileId` int(10) NOT NULL,
				`userId` int(10) NOT NULL,
				`fileType` enum('document','video','audio','image','ppt','other') NOT NULL DEFAULT 'other',
				`fileStorage` enum('local','cloud','net') NOT NULL,
				`fileSource` varchar(32) NOT NULL,
				`createdTime` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130609102237 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
			CREATE TABLE `course_lesson` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` int(10) unsigned NOT NULL,
			  `chapterId` int(10) unsigned NOT NULL DEFAULT '0',
			  `number` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `summary` text NOT NULL,
			  `tags` text NOT NULL,
			  `method` enum('text','video','audio') NOT NULL,
			  `content` text NOT NULL,
			  `media` text NOT NULL,
			  `length` int(11) unsigned NOT NULL DEFAULT '0',
			  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
			  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0',
			  `userId` int(10) unsigned NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    	");

    	$this->addSql("
			CREATE TABLE `course_chapter` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `courseId` int(10) unsigned NOT NULL,
			  `number` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `createdTime` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

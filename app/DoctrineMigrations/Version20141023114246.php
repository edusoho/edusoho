<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141023114246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("CREATE TABLE IF NOT EXISTS `course_draft` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `title` varchar(255) NOT NULL,
	  `summary` text ,
	  `courseId` int(10) unsigned NOT NULL,
	  `content` text ,
	  `userId` int(10) unsigned NOT NULL,
	  `lessonId` int(10) unsigned NOT NULL,
	  `createdTime` int(10) unsigned NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

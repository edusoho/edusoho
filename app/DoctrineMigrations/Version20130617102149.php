<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130617102149 extends AbstractMigration
{
    public function up(Schema $schema)
    {
		$this->addsql("
			CREATE TABLE IF NOT EXISTS `course_announcement` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `userId` int(10) NOT NULL,
			  `courseId` int(10) NOT NULL,
			  `content` text NOT NULL,
			  `createdTime` int(10) NOT NULL,
			  `updatedTime` int(10) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");    	
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

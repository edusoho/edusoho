<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140918153845 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("
	        CREATE TABLE `user_relation` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `fromId` int(10) unsigned NOT NULL ,
				  `toId` int(10) unsigned NOT NULL ,
				  `type` enum('family') NOT NULL,
				  `relation` varchar(255) NOT NULL,
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

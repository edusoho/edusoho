<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140709233702 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE IF NOT EXISTS `theme_config` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL DEFAULT '',
		  `config` text NOT NULL,
		  `confirmConfig` text NOT NULL,
		  `updatedTime` int(11) NOT NULL DEFAULT '0',
		  `createdTime` int(11) NOT NULL DEFAULT '0',
		  `updatedUserId` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

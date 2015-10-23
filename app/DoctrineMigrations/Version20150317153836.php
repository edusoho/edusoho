<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150317153836 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE IF NOT EXISTS `user_common_admin` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `userId` int(10) unsigned NOT NULL,
          `title` varchar(255) NOT NULL DEFAULT '',
          `url` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

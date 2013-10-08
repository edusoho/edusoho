<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130603095637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
                DROP TABLE IF EXISTS `friend`;
                CREATE TABLE IF NOT EXISTS `friend` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `fromId` int(10) unsigned NOT NULL,
                  `toId` int(10) unsigned NOT NULL,
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ");
    }

    public function down(Schema $schema)
    {

    }
}

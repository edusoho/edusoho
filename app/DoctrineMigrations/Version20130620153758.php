<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130620153758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP TABLE IF EXISTS `user_token`;
            CREATE TABLE IF NOT EXISTS `user_token` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `token` varchar(64) NOT NULL,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `type` varchar(255) NOT NULL,
              `data` text NOT NULL,
              `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `token` (`token`(6))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

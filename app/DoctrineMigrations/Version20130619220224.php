<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130619220224 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP TABLE IF EXISTS `user_fortune_log`;
            CREATE TABLE IF NOT EXISTS `user_fortune_log` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `userId` int(11) NOT NULL,
              `number` int(10) NOT NULL,
              `action` varchar(20) NOT NULL,
              `note` varchar(20) NOT NULL,
              `createdTime` int(11) NOT NULL,
              `type` varchar(20) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

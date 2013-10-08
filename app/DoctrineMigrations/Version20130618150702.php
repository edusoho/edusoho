<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130618150702 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `user`  ADD  `unreadNotificationNum` int(10) unsigned NOT NULL DEFAULT '0'  AFTER `createdTime`;");
        $this->addSql("
            DROP TABLE IF EXISTS `notification`;
            CREATE TABLE IF NOT EXISTS `notification` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL,
              `type` varchar(64) NOT NULL,
              `content` text NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        $this->addSql("ALTER TABLE  `notification` CHANGE  `type`  `type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'default';");
        $this->addSql("ALTER TABLE  `notification` ADD  `isRead` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `createdTime`;");
    }

    public function down(Schema $schema)
    {

    }
}

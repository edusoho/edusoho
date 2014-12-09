<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141202104230 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `groups_thread` ADD `rewardCoin` INT(10) UNSIGNED NOT NULL DEFAULT '0' ;");
        $this->addSql("ALTER TABLE `groups_thread` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'default' AFTER `rewardCoin`;");
        $this->addSql("ALTER TABLE `groups_thread_post` ADD `adopt` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `createdTime`;");
        $this->addSql("CREATE TABLE IF NOT EXISTS `groups_thread_trade` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `threadId` int(10) unsigned DEFAULT '0',
          `goodsId` int(10) DEFAULT '0',
          `userId` int(10) unsigned NOT NULL,
          `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `groups_thread_goods` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `title` text NOT NULL,
        `description` text,
        `userId` int(10) unsigned NOT NULL DEFAULT '0',
        `type` enum('content','attachment','postAttachment') NOT NULL,
        `threadId` int(10) unsigned NOT NULL,
        `postId` int(10) unsigned NOT NULL DEFAULT '0',
        `coin` int(10) unsigned NOT NULL,
        `fileId` int(10) unsigned NOT NULL DEFAULT '0',
        `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
        `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150918112009 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isTableExist('keyword')) {
            $this->addSql(" CREATE TABLE `keyword` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(64) CHARACTER SET utf8 NOT NULL,
              `bannedNum` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        }

        if (!$this->isTableExist('keyword_banlog')) {
            $this->addSql("CREATE TABLE `keyword_banlog` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `keywordId` int(10) unsigned NOT NULL,
              `keywordName` varchar(64) NOT NULL DEFAULT '',
             `text` text NOT NULL,
              `userId` int(10) unsigned NOT NULL DEFAULT '0',
              `ip` varchar(64) NOT NULL DEFAULT '',
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `keywordId` (`keywordId`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}

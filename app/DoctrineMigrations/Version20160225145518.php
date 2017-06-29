<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160225145518 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `discovery_column` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                         `title` varchar(255) NOT NULL,
                         `type` varchar(32) NOT NULL,
                         `categoryId` int(10) NOT NULL DEFAULT '0',
                         `orderType` varchar(32) NOT NULL,
                         `showCount` int(10) NOT NULL,
                         `seq` int(10) unsigned NOT NULL DEFAULT '0',
                         `createdTime` int(10) unsigned NOT NULL,
                         `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
                         PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

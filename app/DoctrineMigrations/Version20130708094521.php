<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130708094521 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        
        $this->addSql("
                        DROP TABLE IF EXISTS `block`;
                        CREATE TABLE IF NOT EXISTS `block` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `userId` int(11) NOT NULL COMMENT '用户Id',
                          `title` varchar(255) NOT NULL COMMENT '编辑时的题目',
                          `content` text NOT NULL COMMENT '编辑区的内容',
                          `code` varchar(255) NOT NULL,
                          `createdTime` int(11) unsigned NOT NULL,
                          `updateTime` int(11) unsigned NOT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `code` (`code`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
         ");

        $this->addSql("
                    DROP TABLE IF EXISTS `block_history`;
                    CREATE TABLE IF NOT EXISTS `block_history` (
                    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
                    `blockId` int(11) NOT NULL COMMENT 'blockId',
                    `content` text NOT NULL COMMENT 'content',
                    `userId` int(11) NOT NULL COMMENT 'userId',
                    `createdTime` int(11) unsigned NOT NULL COMMENT 'createdTime',
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='历史表' AUTO_INCREMENT=1 ;
         ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

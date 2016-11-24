<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116102956 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            DROP TABLE IF EXISTS `tag_owner`;
            CREATE TABLE `tag_owner` (
                `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                `ownerType` varchar(255) NOT NULL DEFAULT '' COMMENT '标签拥有者类型',
                `ownerId` int(10) NOT NULL DEFAULT 0 COMMENT '标签拥有者id',
                `tagId` int(10) NOT NULL DEFAULT 0 COMMENT '标签id',
                `userId` int(10) NOT NULL DEFAULT 0 COMMENT '操作用户id',
                `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';
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

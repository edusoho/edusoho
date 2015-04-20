<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150416111909 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_note` ADD `likeNum` INT(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞人数' AFTER `length`;");
        $this->addSql("CREATE TABLE `course_note_like` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `noteId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            `truename` varchar(255) NOT NULL COMMENT '名称',
            `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

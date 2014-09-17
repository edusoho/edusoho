<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140916144628 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE `course_note_praise` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `noteId` int(11) NOT NULL,
                `userId` int(11) NOT NULL,
                `truename` varchar(255) NOT NULL COMMENT '名称',
                `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );  
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

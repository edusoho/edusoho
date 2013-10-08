<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130529103833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `course_review` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(10) unsigned NOT NULL,
            `courseId` int(10) unsigned NOT NULL,
            `score` float unsigned NOT NULL DEFAULT '0.00',
            `rating` float unsigned NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
        );

        $this->addSql(
            "ALTER TABLE  `course` ADD  `score` FLOAT UNSIGNED NOT NULL DEFAULT  '0.00' COMMENT  '分数' AFTER  `price`;"
        );
        $this->addSql(
            "ALTER TABLE  `course` ADD  `rating` FLOAT UNSIGNED NOT NULL DEFAULT  '0.00' COMMENT  '排行数值' AFTER  `score`"
        );
        $this->addSql(
            "ALTER TABLE  `course` ADD  `voteNum` int(10) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '投票人数' AFTER  `rating`"
        );
        
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

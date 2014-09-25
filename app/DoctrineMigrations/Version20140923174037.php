<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140923174037 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `class_member` 
        	ADD `lastRank` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '上次排名' AFTER `title`,
        	ADD `currentRank` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '这次排名' AFTER `lastRank`,
        	ADD `lastRankChangeTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上次排名的值修改时间' AFTER `currentRank`,
        	ADD `rate` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '排名比率' AFTER `lastRankChangeTime`;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

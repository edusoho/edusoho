<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140703105507 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE  `homework_item` ADD  `score` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `questionId`");
        $this->addSql("ALTER TABLE  `homework_item` ADD  `parentId` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `score`");
        $this->addSql("ALTER TABLE  `homework_item` CHANGE  `seq`  `seq` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '题目顺序'");
        $this->addSql("ALTER TABLE  `homework_item` CHANGE  `homeworkId`  `homeworkId` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '所属作业'");
        $this->addSql("ALTER TABLE  `homework_item` ADD  `missScore` FLOAT( 10, 1 ) NOT NULL DEFAULT  '0' COMMENT  '漏选得分' AFTER  `score`");
        $this->addSql("ALTER TABLE  `homework_item` CHANGE  `score`  `score` FLOAT( 10, 1 ) UNSIGNED NOT NULL DEFAULT  '0'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

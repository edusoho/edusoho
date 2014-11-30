<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141130142505 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE `question` ADD `mainKnowledgeId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主知识点id' AFTER `categoryId`;");
    	$this->addSql("ALTER TABLE `question` ADD `relatedKnowledgeIds` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '关联知识点ID' AFTER `mainKnowledgeId`;");
    	$this->addSql("AALTER TABLE `question` ADD `tagIds` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'tagsIds' AFTER `relatedKnowledgeIds`;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

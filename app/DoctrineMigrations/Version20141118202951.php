<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141118202951 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("
    		ALTER TABLE `knowledge` DROP `categoryId`;
			ALTER TABLE `knowledge` ADD `gradeId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '年级id' AFTER `parentId`;
			ALTER TABLE `knowledge` ADD `subjectId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '科目id' AFTER `gradeId`;
			ALTER TABLE `knowledge` ADD `materialId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教材id' AFTER `parentId`;
			ALTER TABLE `knowledge` ADD `term` ENUM('first', 'second') CHARACTER SET utf32 COLLATE utf32_general_ci NOT NULL DEFAULT 'first' COMMENT '学期' AFTER `parentId`;
    	");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

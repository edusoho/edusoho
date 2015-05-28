<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150127114241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        ALTER TABLE `thread` CHANGE `targetaType` `targetType` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'classroom' COMMENT '所属 类型';
        ");
        $this->addSql("
        ALTER TABLE `thread_post` ADD `targetType` VARCHAR(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型', ADD `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属 类型ID';   ");
        
        $this->addSql("
        ALTER TABLE `thread` ADD `relationId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '从属ID' AFTER `targetId` , ADD `categoryId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID' AFTER `relationId` ; ");

     }
        
   
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

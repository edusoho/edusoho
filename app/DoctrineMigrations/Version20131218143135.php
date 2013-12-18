<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20131218143135 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSQL("
    		ALTER TABLE  `content` ADD  `editor` ENUM(  'kindeditor',  'none' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'kindeditor' COMMENT '编辑器选择类型字段' AFTER  `title`;
    		");
    }

    public function down(Schema $schema)
    {

    }
}

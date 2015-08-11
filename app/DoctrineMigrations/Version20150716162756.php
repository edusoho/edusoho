<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150716162756 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addsql("ALTER TABLE `classroom_member` CHANGE `role` `role` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'auditor' COMMENT '角色'");
    	$this->addsql("ALTER TABLE `classroom` ADD `assistantIds` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ALTER `teacherIds`");
        $this->addsql("UPDATE `classroom_member` SET `role` = concat('|',role,'|')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

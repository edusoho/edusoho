<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160525165606 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            ALTER TABLE `article` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `course` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `classroom` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `user` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `announcement` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `category` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';
            ALTER TABLE `category` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `tag` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';
            ALTER TABLE `tag` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;
            ALTER TABLE `navigation` ADD `orgCode` varchar(255)  NULL DEFAULT '1.' comment '组织机构内部编码';
            ALTER TABLE `navigation` ADD `orgId` INT(10) unsigned NULL DEFAULT '1' AFTER `orgCode`;

        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

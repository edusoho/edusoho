<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151130101505 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `user` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `user` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `course` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `course_lesson` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `thread` ADD INDEX(`updateTime`);");

        $this->addSql("ALTER TABLE `course_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course_thread` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `groups_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `type`;");
        $this->addSql("ALTER TABLE `groups_thread` ADD INDEX `updatedTime` (`updatedTime`);");
        
        $this->addSql("ALTER TABLE `article` ADD INDEX(`updatedTime`);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

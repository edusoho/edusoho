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
        $this->addSql("ALTER TABLE `user` ADD `updatedTime` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间(毫秒)' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `user` ADD INDEX `updatedTime` (`updatedTime`);");
        $this->addSql("UPDATE `user` SET updatedTime = createdTime * 1000;");

        $this->addSql("ALTER TABLE `course` ADD `updatedTime` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间(毫秒)' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course` ADD INDEX `updatedTime` (`updatedTime`);");
        $this->addSql("UPDATE `course` SET updatedTime = createdTime * 1000;");

        $this->addSql("ALTER TABLE `course_lesson` ADD `updatedTime` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间(毫秒)' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");
        $this->addSql("UPDATE `course_lesson` SET updatedTime = createdTime * 1000;");

        $this->addSql("ALTER TABLE `article` CHANGE `updatedTime` `updatedTime` BIGINT(10) UNSIGNED NOT NULL DEFAULT '0';");
        $this->addSql("UPDATE `article` SET updatedTime = updatedTime * 1000;");

        $this->addSql("ALTER TABLE `course_thread` ADD `updatedTime` BIGINT UNSIGNED NOT NULL DEFAULT '0' AFTER `createdTime`;");
        $this->addSql("ALTER TABLE `course_thread` ADD INDEX `updatedTime` (`updatedTime`);");
        $this->addSql("UPDATE `course_thread` SET updatedTime = createdTime * 1000;");

        $this->addSql("ALTER TABLE `groups_thread` ADD `updatedTime` BIGINT UNSIGNED NOT NULL DEFAULT '0' AFTER `type`;");
        $this->addSql("ALTER TABLE `groups_thread` ADD INDEX `updatedTime` (`updatedTime`);");
        $this->addSql("UPDATE `groups_thread` SET updatedTime = createdTime * 1000;");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

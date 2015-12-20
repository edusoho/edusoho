<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151217164502 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("UPDATE `course_thread` SET  `updatedTime` = `createdTime`;");
        $this->addSql("ALTER TABLE `course_thread` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `thread` ADD INDEX(`updateTime`);");

        $this->addSql("ALTER TABLE `groups_thread` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `type`;");
        $this->addSql("UPDATE `groups_thread` SET  `updatedTime` = `createdTime`;");
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

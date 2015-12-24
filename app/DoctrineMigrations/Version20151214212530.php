<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214212530 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `user` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("UPDATE `user` SET  `updatedTime` = `createdTime`;");
        $this->addSql("ALTER TABLE `user` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `course` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("UPDATE `course` SET  `updatedTime` = `createdTime`;");
        $this->addSql("ALTER TABLE `course` ADD INDEX `updatedTime` (`updatedTime`);");

        $this->addSql("ALTER TABLE `course_lesson` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        $this->addSql("UPDATE `course_lesson` SET  `updatedTime` = `createdTime`;");
        $this->addSql("ALTER TABLE `course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

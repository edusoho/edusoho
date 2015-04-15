<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150415103250 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_announcement` RENAME TO `announcement`");
        $this->addSql("ALTER TABLE `announcement` ADD COLUMN `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型' AFTER `userId`");
        $this->addSql("ALTER TABLE `announcement` CHANGE `courseId` `targetId`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型ID'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

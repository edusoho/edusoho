<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160608010216 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `course_member` ADD INDEX `courseId_role_createdTime` (`courseId`, `role`, `createdTime`);");
        $this->addSql("ALTER TABLE `message_conversation` ADD INDEX `toId_fromId` (`toId`, `fromId`);");
        $this->addSql("ALTER TABLE `message_conversation` ADD INDEX `toId_latestMessageTime` (`toId`, `latestMessageTime`);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

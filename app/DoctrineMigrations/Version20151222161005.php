<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151222161005 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `user` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
        $this->addSql("UPDATE `course` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
        $this->addSql("UPDATE `course_lesson` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
        $this->addSql("UPDATE `course_thread` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
        $this->addSql("UPDATE `groups_thread` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
        $this->addSql("UPDATE `thread` SET  `updateTime` = `createdTime` WHERE updateTime is null or updateTime = 0;");
        $this->addSql("UPDATE `article` SET  `updatedTime` = `createdTime` WHERE updatedTime is null or updatedTime = 0;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

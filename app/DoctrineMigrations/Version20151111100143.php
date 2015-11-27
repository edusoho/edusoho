<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151111100143 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `user` ADD `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后修改时间' AFTER `createdTime`; ");

        $this->addSql("ALTER TABLE `course` ADD `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后修改时间' AFTER `createdTime`; ");

        $this->addSql("ALTER TABLE `course_lesson` ADD `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后修改时间' AFTER `createdTime`; ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

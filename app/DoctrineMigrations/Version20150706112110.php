<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150706112110 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("ALTER TABLE `message` ADD `type` varchar(32) NOT NULL DEFAULT 'text' COMMENT '私信类型' AFTER `id`;");
        $this->addSql("ALTER TABLE `message_conversation` ADD `latestMessageType` varchar(32) NOT NULL DEFAULT 'text' COMMENT '最后一条私信类型' AFTER `latestMessageContent`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

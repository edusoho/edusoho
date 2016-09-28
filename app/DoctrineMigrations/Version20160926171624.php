<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160926171624 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("drop table im_my_conversation");

        $this->addSql("ALTER TABLE course CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '课程聊天室ID';");
        $this->addSql("UPDATE course SET `convNo` = '' WHERE `convNo` = '0';");

        $this->addSql("ALTER TABLE classroom CHANGE conversationId convNo VARCHAR(32) NOT NULL DEFAULT ''  COMMENT '班级聊天室ID';");
        $this->addSql("UPDATE classroom SET `convNo` = '' WHERE `convNo` = '0';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

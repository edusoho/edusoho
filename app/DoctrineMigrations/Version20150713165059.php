<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150713165059 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `user` ADD `source` VARCHAR(50)  NOT NULL DEFAULT 'default' COMMENT '来源';");
        $this->addSql("UPDATE `user` SET source = 'discuz' WHERE type = 'discuz';");
        $this->addSql("UPDATE `user` SET source = 'phpwind' WHERE type = 'phpwind';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

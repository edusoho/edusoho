<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140720223611 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `upload_files` ADD  `convertParams` TEXT NULL COMMENT  '文件转换参数' AFTER  `convertStatus`;");
        $this->addSql("ALTER TABLE `upload_files` ADD UNIQUE (`convertHash` ( 32 ));");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150522094750 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `thread` ADD `solved` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否有老师回答(已被解决)' AFTER `sticky`;");
        $this->addSql("ALTER TABLE `thread_post` ADD `adopted` TINYINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否被采纳(是老师回答)' AFTER `content`;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

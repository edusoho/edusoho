<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160419162117 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $sql    = "select max(id) as maxId from upload_files;";
        $result = $this->connection->fetchAssoc($sql);
        $start  = $result['maxId'] + 1000;
        $this->addSql("alter table upload_file_inits AUTO_INCREMENT = {$start};");
        $this->addSql("ALTER TABLE `upload_files` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

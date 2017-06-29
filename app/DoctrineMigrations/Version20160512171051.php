<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160512171051 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql    = "select max(id) as maxId from upload_files;";
        $result = $this->connection->fetchAssoc($sql);

        $start = $result['maxId'] + 1000;
        $this->addSql("alter table upload_file_inits AUTO_INCREMENT = {$start};");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

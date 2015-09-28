<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150921103125 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$this->isTableExist('organization')){
            $this->addSql("CREATE TABLE organization(
                id INT(10) NOT NULL PRIMARY KEY auto_increment,
                name varchar(255) NOT NULL ,
                code varchar(64) NOT NULL ,
                description TEXT,
                parentId INT(10) NOT NULL DEFAULT 0,
                createdTime INT(10));
                ");
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}

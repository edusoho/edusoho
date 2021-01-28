<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150414173841 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE `course` c set income=(select sum(amount) from orders where targetId=c.id and targetType='course' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='course' and status in ('paid','refunding','refunded'));");

        if($this->isTableExist('classroom')) {
            $this->addSql("UPDATE `classroom` c set income=(select sum(amount) from orders where targetId=c.id and targetType='classroom' and status in ('paid','refunding','refunded')) where id in (select distinct targetId from orders where targetType='classroom' and status in ('paid','refunding','refunded'));");
        }
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

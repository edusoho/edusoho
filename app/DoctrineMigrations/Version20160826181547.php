<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160826181547 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE orders o SET payment = 'none' WHERE (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' ORDER BY ol.createdTime DESC LIMIT 1) IS NOT NULL AND userId = (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' ORDER BY ol.createdTime DESC LIMIT 1)  AND o.status='paid' AND o.payment = 'outside';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

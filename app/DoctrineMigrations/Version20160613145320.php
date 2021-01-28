<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160613145320 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE orders MODIFY payment VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '订单支付方式';");
        $this->addSql("UPDATE orders o SET payment = 'outside' WHERE (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' order by ol.createdTime desc limit 1) IS NOT NULL AND userId != (SELECT userId FROM order_log ol WHERE o.id = ol.orderId AND ol.type='created' order by ol.createdTime desc limit 1)  AND o.status='paid';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

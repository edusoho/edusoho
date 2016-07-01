<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160620173333 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('order_referer_log');
        if ($table->hasColumn('createdUser')) {
            $this->addSql("ALTER TABLE `order_referer_log` CHANGE `createdUser` `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付者';");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

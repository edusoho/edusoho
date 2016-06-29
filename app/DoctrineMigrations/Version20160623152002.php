<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160623152002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('order_referer_log');
        if ($table->hasColumn('sourceTargertId')) {
            $this->addSql("ALTER TABLE `order_referer_log` ADD `sourceTargertId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '来源ID' AFTER  `orderId`;");
        }
        $table = $schema->getTable('order_referer_log');
        if ($table->hasColumn('sourceTargertType')) {
            $this->addSql("ALTER TABLE `order_referer_log` ADD `sourceTargertType` varchar(64) NOT NULL DEFAULT ''  COMMENT '来源类型' AFTER  `sourceTargertId`;");
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

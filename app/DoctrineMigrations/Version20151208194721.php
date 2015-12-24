<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151208194721 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        if (!$this->isFieldExist('cash_orders', 'targetType')) {
            $this->addSql("ALTER TABLE `cash_orders` ADD `targetType` VARCHAR(64) NOT NULL DEFAULT 'coin' COMMENT '订单类型'");
        }

        if (!$this->isFieldExist('cash_orders', 'token')) {
            $this->addSql("ALTER TABLE `cash_orders` ADD `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌';");
        }

        if (!$this->isFieldExist('cash_orders', 'data')) {
            $this->addSql("ALTER TABLE `cash_orders` ADD `data` TEXT NULL DEFAULT NULL COMMENT '订单业务数据'");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

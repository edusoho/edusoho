<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151022152630 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay') CHARACTER SET utf8 NOT NULL");
        $this->addSql("ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay') CHARACTER SET utf8 NULL DEFAULT NULL");
        $this->addSql("ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay') CHARACTER SET utf8 NOT NULL");

        if (!$this->isFieldExist('orders', 'token')) {
            $this->addSql("ALTER TABLE `orders` ADD `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌'");
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

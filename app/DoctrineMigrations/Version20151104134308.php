<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151104134308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(" ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $this->addSql(" ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $this->addSql(" ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL"); 
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
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}

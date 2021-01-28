<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

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
        $this->addSql(" ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay') CHARACTER SET utf8  NOT NULL");
        $this->addSql(" ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 NOT NULL");
        $this->addSql(" ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay','quickpay') CHARACTER SET utf8  NULL DEFAULT NULL");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `user_pay_agreement` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL COMMENT '用户Id',
            `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
            `bankName` varchar(255) NOT NULL COMMENT '银行名称',
            `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
            `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
            `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
            `otherId` int(8) NOT NULL COMMENT '对应的银行Id',
            `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
            `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行'"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151019105953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->isTableExist('card_bag')){
            $this->addSql("CREATE TABLE `card_bag` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `cardId` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '卡的ID',
                  `cardType` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '卡的类型',
                  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '卡密',
                  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
                  `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
                  `status` enum('normal','invalid','recharged') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'invalid' COMMENT '使用状态',
                  `userId` int(11) NOT NULL DEFAULT '0' COMMENT '使用者',
                  `batchId` int(11) NOT NULL DEFAULT '0' COMMENT '批次ID',
                  `targetType` varchar(64) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '使用对象类型',
                  `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '使用对象',
                  `couponType` enum('minus','discount') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '优惠码类型',
                  `rate` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
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

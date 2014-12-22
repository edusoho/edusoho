<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141217150758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `carts` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `itemId` int(10) unsigned NOT NULL COMMENT '被购者Id',
              `itemType` varchar(128) NOT NULL COMMENT '被购者类型',
              `number` int(10) unsigned NOT NULL COMMENT '数量',
              `userId` int(10) unsigned NOT NULL COMMENT '购买者Id',
              `createdTime` int(10) unsigned NOT NULL COMMENT '购买时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

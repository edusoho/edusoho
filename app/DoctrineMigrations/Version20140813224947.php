<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140813224947 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE `mobile_device` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设备ID',
              `imei` varchar(255) NOT NULL COMMENT '串号',
              `platform` varchar(255) NOT NULL COMMENT '平台',
              `version` varchar(255) NOT NULL COMMENT '版本',
              `screenresolution` varchar(100) NOT NULL COMMENT '分辨率',
              `kernel` varchar(255) NOT NULL COMMENT '内核',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

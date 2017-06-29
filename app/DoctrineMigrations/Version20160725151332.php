<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160725151332 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS `order_referer`;
            CREATE TABLE `order_referer` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `uv` VARCHAR(64) NOT NULL ,
              `data` text NOT NULL ,
              `orderIds` text,
              `expiredTime`  int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '过期时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户访问日志Token';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

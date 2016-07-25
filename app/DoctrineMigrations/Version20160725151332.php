<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

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
        $this->addSql("DROP TABLE IF EXISTS `referer_order_token`;
            CREATE TABLE `referer_order_token` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `token` int(11) NOT NULL COMMENT '模块ID',
              `ip` varchar(64) NOT NULL,
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问时间',
              `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问者',
              `expiredTime`  int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '过期时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='模块(课程|班级|公开课|...)的访问来源日志';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

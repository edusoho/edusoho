<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151028135212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `recent_post_num` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                 `ip` varchar(20) NOT NULL COMMENT 'IP',
                 `type` varchar(255) NOT NULL COMMENT '类型',
                 `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
                 `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
                 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

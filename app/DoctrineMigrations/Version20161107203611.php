<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161107203611 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            DROP TABLE IF EXISTS `tag_group`;
            CREATE TABLE `tag_group` (
                `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组名字',
                `scope` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组应用范围',
                `tagNum` int(10) NOT NULL DEFAULT '0' COMMENT '标签组里的标签数量',
                `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
                `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组表';
        ");

        $this->addSql("ALTER TABLE classroom ADD `tags` text NOT NULL COMMENT '被打上的标签'");

        $this->addSql("
            DROP TABLE IF EXISTS `tag_group_tag`;
            CREATE TABLE `tag_group_tag` (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
                `groupId` int(10) NOT NULL DEFAULT '0' COMMENT '标签组ID',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组跟标签的中间表';
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

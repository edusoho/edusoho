<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141124103959 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `articel_material` (
                `id` int(10) unsigned NOT NULL COMMENT 'id',
                `title` varchar(256) NOT NULL COMMENT '标题',
                `content` text COMMENT '内容',
                `mainKnowledgeId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主知识点id',
                `relatedKnowledgeIds` varchar(255) NOT NULL COMMENT '关联知识点ids',
                `knowledgeIds` varchar(255) NOT NULL COMMENT '知识点ids',
                `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类Id',
                `tagIds` varchar(256) NOT NULL COMMENT '标签ids',
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户Id',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                KEY `mainKnowledgeId` (`mainKnowledgeId`,`categoryId`,`userId`,`updatedUserId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章素材表';
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

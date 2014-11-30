<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141125144925 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `essay` (
              `id` int(10) NOT NULL,
              `title` varchar(256) NOT NULL COMMENT '标题',
              `description` text COMMENT '文章描述',
              `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类Id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户Id',
              `status` enum('published','unpublished') NOT NULL DEFAULT 'unpublished' COMMENT '状态',
              `source` varchar(256) NOT NULL COMMENT '来源',
              `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
              `publishedTime` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`,`categoryId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章管理表';
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `essay_chapter` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `articleId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
              `type` enum('chapter','unit') NOT NULL DEFAULT 'chapter' COMMENT '类型',
              `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
              `seq` int(10) unsigned NOT NULL COMMENT '排序',
              `title` varchar(256) NOT NULL COMMENT '名称',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `articleId` (`articleId`,`parentId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章章节表' AUTO_INCREMENT=1 ;
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `essay_relation` (
              `id` int(10) unsigned NOT NULL COMMENT 'id',
              `articleId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
              `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '章节id',
              `materialId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '材料id',
              `seq` int(10) unsigned NOT NULL COMMENT '排序id',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户id',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `articleId` (`articleId`,`chapterId`,`materialId`,`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章关联表';
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

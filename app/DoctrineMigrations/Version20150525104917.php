<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150525104917 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `article_like` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
 `articleId` int(10) unsigned NOT NULL COMMENT '资讯id',
 `userId` int(10) unsigned NOT NULL COMMENT '用户id',
 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞时间',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='资讯点赞表';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

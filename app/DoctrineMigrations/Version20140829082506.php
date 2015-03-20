<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140829082506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE `status` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '动态发布的人',
              `type` varchar(64) NOT NULL COMMENT '动态类型',
              `objectType` varchar(64) NOT NULL DEFAULT '' COMMENT '动态对象的类型',
              `objectId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态对象ID',
              `message` text NOT NULL COMMENT '动态的消息体',
              `properties` text NOT NULL COMMENT '动态的属性',
              `commentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
              `likeNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被赞的数量',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态发布时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `createdTime` (`createdTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

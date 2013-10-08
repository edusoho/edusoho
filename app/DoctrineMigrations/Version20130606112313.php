<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130606112313 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("
			CREATE TABLE IF NOT EXISTS `course_note` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `userId` int(10) NOT NULL COMMENT '笔记作者ID',
			  `courseId` int(10) NOT NULL COMMENT '课程ID',
			  `lessonId` int(10) NOT NULL COMMENT '课时ID',
			  `content` text NOT NULL COMMENT '笔记内容',
			  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
			  `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
			  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

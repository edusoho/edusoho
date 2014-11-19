<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141117114554 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
			CREATE TABLE IF NOT EXISTS `courseware` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL COMMENT '标题',
			  `url` varchar(1024) NOT NULL COMMENT '文件url',
			  `type` enum('document','video','audio','image','ppt') NOT NULL DEFAULT 'video' COMMENT '文件类型',
			  `image` varchar(1024) NOT NULL COMMENT '截图',
			  `mainKnowledgeId` int(10) NOT NULL COMMENT '主知识点',
			  `relatedKnowledgeIds` varchar(255) NOT NULL COMMENT '关联知识点',
			  `tagIds` varchar(255) NOT NULL COMMENT '标签',
			  `source` varchar(255) NOT NULL COMMENT '来源',
			  `createdId` int(10) NOT NULL COMMENT '创建人Id',
			  `createdTime` int(10) NOT NULL COMMENT '创建时间',
			  PRIMARY KEY (`id`),
			  KEY `mainKnowledgeId` (`mainKnowledgeId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课件表' AUTO_INCREMENT=1 ;
    	");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

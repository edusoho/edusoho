<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160509150945 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS  `org` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组织机构ID',
			  `name` varchar(255) NOT NULL COMMENT '名称',
			  `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '组织机构父ID',
			  `childrenNum` tinyint(3) unsigned NOT NULL  DEFAULT  '0' COMMENT '辖下组织机构数量',
			  `depth` int(11) NOT NULL   DEFAULT  '1' COMMENT '当前组织机构层级',
			  `seq` int(11) NOT NULL COMMENT '索引',
			  `description` text COMMENT '备注',
			  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '机构编码',
			  `orgCode` varchar(255) NOT NULL DEFAULT '0' COMMENT '内部编码',
			  `createdUserId` int(11) NOT NULL COMMENT '创建用户ID',
			  `createdTime` int(11) unsigned NOT NULL  COMMENT '创建时间',
			  `updateTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '最后更新时间',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `orgCode` (`orgCode`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组织机构';"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

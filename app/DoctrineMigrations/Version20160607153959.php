<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160607153959 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            "CREATE TABLE `block_template` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '模版ID',
                  `title` varchar(255) NOT NULL COMMENT '标题',
                  `mode` ENUM('html','template') NOT NULL DEFAULT 'html' COMMENT '模式' ,
                  `template` text COMMENT '模板',
                  `templateName` VARCHAR(255)  COMMENT '编辑区模板名字',
                  `templateData` text  COMMENT '模板数据',
                  `content` text COMMENT '默认内容',
                  `data` text COMMENT '编辑区内容',
                  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
                  `meta` text  COMMENT '编辑区元信息',
                  `tips` VARCHAR( 255 ),
                  `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)',
                  `createdTime` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
                  `updateTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `code` (`code`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='编辑区模板';"
        );
        $this->addSql("ALTER TABLE  `block` ADD  `orgId` INT(11) NOT NULL  DEFAULT 1 COMMENT '组织机构Id'");
        $this->addSql("ALTER TABLE  `block` ADD  `blockTemplateId` INT(11) NOT NULL COMMENT '模版ID'");
        $this->addSql("INSERT INTO `block_template`( `title`, `mode`,`template`,`templateName`,`templateData`,`content`,`data`,`code`, `meta`, `tips`, `category`, `createdTime`,`updateTime`) select `title`, `mode`,`template`,`templateName`,`templateData`,`content`,`data`,`code`, `meta`, `tips`, `category`, `createdTime`,`updateTime` from `block`;");
        $this->addSql("UPDATE `block` join `block_template` on block.code = block_template.code SET block.blockTemplateId = block_template.id;");
        $this->addSql("ALTER TABLE `block` DROP INDEX `code`");
        $this->addSql("ALTER TABLE `block` DROP `mode`, DROP `template`, DROP `title`, DROP `templateName`, DROP `templateData`, DROP `meta`, DROP `tips`, DROP `category`");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

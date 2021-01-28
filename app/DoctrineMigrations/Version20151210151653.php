<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151210151653 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `marker` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `second` int(10) unsigned NOT NULL COMMENT '驻点时间',
              `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='驻点';

            CREATE TABLE IF NOT EXISTS `question_marker` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                `questionId` int(10) unsigned NOT NULL COMMENT '问题Id',
                `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
                `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
                `stem` text COMMENT '题干',
                `answer` text COMMENT '参考答案',
                `analysis` text COMMENT '解析',
                `metas` text COMMENT '题目元信息',
                `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='弹题';

            CREATE TABLE IF NOT EXISTS `question_marker_result` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
                `questionMarkerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹题ID',
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
                `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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

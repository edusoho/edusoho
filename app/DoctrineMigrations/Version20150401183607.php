<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150401183607 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE `crontab_job`;");
        $this->addSql("
            CREATE TABLE `crontab_job` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `name` varchar(1024) NOT NULL COMMENT '任务名称',
              `cycle` enum('once') NOT NULL DEFAULT 'once' COMMENT '任务执行周期',
              `jobClass` varchar(1024) NOT NULL COMMENT '任务的Class名称',
              `jobParams` text NOT NULL COMMENT '任务参数',
              `executing` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行状态',
              `nextExcutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
              `latestExecutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后执行的时间',
              `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
              `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
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

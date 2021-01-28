<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150828182832 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `task` (
              `id` int(10) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) DEFAULT NULL COMMENT '任务标题',
              `description` text COMMENT '任务描述',
              `meta` text COMMENT '任务元信息',
              `userId` int(10) NOT NULL DEFAULT '0',
              `taskType` varchar(100) NOT NULL COMMENT '任务类型',
              `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '批次Id',
              `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '类型id,可以是课时id,作业id等',
              `targetType` varchar(100) DEFAULT NULL COMMENT '类型,可以是课时,作业等',
              `taskStartTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务开始时间',
              `taskEndTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务结束时间',
              `status` enum('active','completed') NOT NULL DEFAULT 'active',
              `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必做任务,0否,1是',
              `completedTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务完成时间',
              `createdTime` int(10) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
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

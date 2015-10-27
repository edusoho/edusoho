<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151026174121 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `course_score_setting` (
                `courseId` int(10) unsigned NOT NULL COMMENT 'course id主键',
                `credit` int(10) unsigned COMMENT '可获得学分',
                `examWeight` int(10) unsigned NOT NULL COMMENT '考试权重',
                `homeworkWeight` int(10) unsigned NOT NULL COMMENT '作业权重',
                `otherWeight` int(10) unsigned NOT NULL COMMENT '其它分权重',
                `standardScore` int(10) unsigned NOT NULL COMMENT '达标分数',
                `expectPublishTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '预计发布时间',
                `publishType` enum('manual','auto') NOT NULL DEFAULT 'manual' COMMENT '发布类型：手动发布，自动发布',
                `status` enum('unpublish','published') NOT NULL DEFAULT 'unpublish' COMMENT '成绩状态',
                `publishTime` int(10) unsigned COMMENT '发布时间',
                `createdTime` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程评分标准表';
            
            CREATE TABLE IF NOT EXISTS `course_member_score` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '成绩单ID',
                `courseId` int(10) unsigned NOT NULL COMMENT 'course id',
                `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
                `totalScore` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                `examScore` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '考试成绩',
                `homeworkScore` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '作业成绩',
                `otherScore` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '其它成绩',
                `createdTime` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程学员得分表';
            
        "
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

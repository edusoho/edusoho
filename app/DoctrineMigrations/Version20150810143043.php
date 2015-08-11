<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150810143043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `homework_review` (
                `id` int(10) unsigned NOT NULL auto_increment ,
                `userId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评分用户id',
                `homeworkId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业id',
                `homeworkResultId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业答卷id',
                `category` enum('teacher','student') DEFAULT 'student' COMMENT '评分分类:学员互评/老师评分',
                `score` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分数',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
                PRIMARY KEY  (`id`)
            ) comment='作业评分' ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `homework_review_item` (
                `id` int(10) unsigned NOT NULL auto_increment ,
                `homeworkItemResultId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '答题id',
                `homeworkReviewId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业评分id',
                `score` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分数',
                `review` varchar(64) COMMENT '点评' ,
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
                PRIMARY KEY  (`id`)
            ) comment='答题评分' ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
        $this->addSql("ALTER TABLE `homework_result` ADD `studentScore` int(10) unsigned  COMMENT '同学评分(互评成绩)';");
        $this->addSql("ALTER TABLE `homework_result` ADD `teacherScore` int(10) unsigned  COMMENT '老师评分';");
        $this->addSql("ALTER TABLE `homework` ADD `completeTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作业完成时间';");
        $this->addSql("ALTER TABLE `homework` ADD `reviewEndTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '互评结束时间';");
        $this->addSql("ALTER TABLE `homework` ADD `comment` TINYINT unsigned NOT NULL DEFAULT  '0' COMMENT '是否启用互评';");
        $this->addSql("ALTER TABLE `homework` ADD `completePercent` text unsigned NOT NULL COMMENT '作业成绩占比:完成互评的';");
        $this->addSql("ALTER TABLE `homework` ADD `partPercent` text unsigned NOT NULL COMMENT '作业成绩占比:部分互评的';");
        $this->addSql("ALTER TABLE `homework` ADD `zeroPercent` text unsigned NOT NULL COMMENT '作业成绩占比:没有参与互评的';");
        $this->addSql("ALTER TABLE `homework` ADD `minReviewers` text unsigned NOT NULL COMMENT '最少互评人数';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('homework_review');
        $schema->dropTable('homework_review_item');
        $this->addSql("ALTER TABLE `homework_result` DROP `studentScore`;");
        $this->addSql("ALTER TABLE `homework_result` DROP `teacherScore`;");
        $this->addSql("ALTER TABLE `homework` DROP `completeTime`;");
        $this->addSql("ALTER TABLE `homework` DROP `reviewEndTime`;");
        $this->addSql("ALTER TABLE `homework` DROP `comment`;");
        $this->addSql("ALTER TABLE `homework` DROP `completePercent`;");
        $this->addSql("ALTER TABLE `homework` DROP `partPercent`;");
        $this->addSql("ALTER TABLE `homework` DROP `zeroPercent`;");
        $this->addSql("ALTER TABLE `homework` DROP `minReviewers`;");
    }
}

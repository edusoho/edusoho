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
            "CREATE TABLE IF NOT EXISTS `homework_score` (
                `id` int(10) unsigned NOT NULL auto_increment ,
                `userId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评分用户id',
                `homeworkId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业id',
                `homeworkResultId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业答卷id',
                `category` enum('teacher','student') DEFAULT 'student' COMMENT '评分分类:学员互评/老师评分',
                `score` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分数',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
                PRIMARY KEY  (`id`)
            ) comment='作业评分' ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;") ; 

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `homework_item_score` (
                `id` int(10) unsigned NOT NULL auto_increment ,
                `homeworkItemResultId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '答题id',
                `homeworkScoreId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '作业id',
                `score` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分数',
                `review` varchar(64) COMMENT '点评' ,
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0',       
                PRIMARY KEY  (`id`)
            ) comment='答题评分' ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;") ; 
            $this->addSql("ALTER TABLE `homework_result` ADD `studentScore` int(10) unsigned  COMMENT '同学评分(互评成绩)';");
            $this->addSql("ALTER TABLE `homework_result` ADD `teacherScore` int(10) unsigned  COMMENT '老师评分';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
       $schema->dropTable('homework_score');
       $schema->dropTable('homework_item_score');
       $this->addSql("ALTER TABLE `homework_result` DROP `studentScore`;");
       $this->addSql("ALTER TABLE `homework_result` DROP `teacherScore`;");
    }
}

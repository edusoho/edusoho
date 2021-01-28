<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20131224141953 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
        	DROP TABLE IF EXISTS `quiz_question`;
			CREATE TABLE `quiz_question` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `questionType` enum('choice','single_choice','fill','material','essay','determine') NOT NULL,
			  `stem` text NOT NULL COMMENT '题干',
			  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分数',
			  `answer` text NOT NULL COMMENT '参考答案',
			  `analysis` text COMMENT '解析',
			  `categoryId` int(11) NOT NULL DEFAULT '0' COMMENT '类别',
			  `difficulty` enum('simple','ordinary','difficulty') NOT NULL,
			  `targetId` int(11) NOT NULL COMMENT '从属于',
			  `targetType` enum('lesson','course','subject') NOT NULL COMMENT '从属类型：课时、课程、科目',
			  `parentId` int(10) unsigned DEFAULT '0' COMMENT '材料父ID',
			  `finishedTimes` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '完成次数',
			  `passedTimes` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
			  `userId` int(11) NOT NULL COMMENT '用户id',
			  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
			  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='问题表';

			DROP TABLE IF EXISTS `quiz_question_category`;
			CREATE TABLE `quiz_question_category` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL COMMENT '类别名称',
			  `targetId` int(11) NOT NULL COMMENT '从属课程、科目id',
			  `targetType` enum('course','subject','lesson') NOT NULL COMMENT '从属课程、科目',
			  `userId` int(11) NOT NULL COMMENT '操作用户',
			  `updatedTime` int(11) NOT NULL COMMENT '更新时间',
			  `createdTime` int(11) NOT NULL COMMENT '创建时间',
			  `seq` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库类别表';

			DROP TABLE IF EXISTS `quiz_question_choice`;
			CREATE TABLE `quiz_question_choice` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
			  `questionId` bigint(20) NOT NULL COMMENT 'quesitonId',
			  `content` text NOT NULL COMMENT 'content',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			DROP TABLE IF EXISTS `test_item`;
			CREATE TABLE `test_item` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '题目',
			  `testId` int(11) NOT NULL COMMENT '所属试卷',
			  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
			  `questionId` int(11) NOT NULL COMMENT '题目id',
			  `questionType` enum('choice','single_choice','fill','material','essay','determine') NOT NULL COMMENT '题目类别',
			  `parentId` int(11) NOT NULL DEFAULT '0',
			  `score` int(11) NOT NULL DEFAULT '0' COMMENT '分值',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			DROP TABLE IF EXISTS `test_item_result`;
			CREATE TABLE `test_item_result` (
			  `itemId` int(11) NOT NULL COMMENT '试卷题目id',
			  `parentId` int(11) NOT NULL COMMENT '用户的测验id',
			  `testId` int(11) NOT NULL COMMENT '试卷id',
			  `answer` text COMMENT '答案',
			  `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '分数',
			  `teacherSay` text COMMENT '教师批注',
			  `questionId` int(11) NOT NULL COMMENT '题库题目id',
			  PRIMARY KEY (`itemId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			DROP TABLE IF EXISTS `test_paper`;
			CREATE TABLE `test_paper` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
			  `name` varchar(255) NOT NULL COMMENT '试卷名称',
			  `description` text COMMENT '试卷说明',
			  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：\r\n秒)',
			  `targetId` int(11) NOT NULL COMMENT '试卷从属',
			  `targetType` enum('course','subject','unit','lesson') NOT NULL COMMENT '从\r\n属类别',
			  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状\r\n态：draft,open,closed',
			  `score` int(11) NOT NULL COMMENT '总分',
			  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
			  `createdUserId` int(11) DEFAULT NULL COMMENT '创建人',
			  `createdTime` int(11) NOT NULL COMMENT '创建时间',
			  `updatedUserId` int(11) NOT NULL COMMENT '修改人',
			  `updatedTime` int(11) NOT NULL COMMENT '修改时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

			DROP TABLE IF EXISTS `test_paper_result`;
			CREATE TABLE `test_paper_result` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
			  `testId` int(11) NOT NULL COMMENT 'testId',
			  `userId` int(11) NOT NULL COMMENT 'UserId',
			  `score` int(11) NOT NULL COMMENT '分数',
			  `finishedTime` int(11) NOT NULL COMMENT '完成时间（秒）',
			  `beginTime` int(11) NOT NULL COMMENT '开始时间',
			  `endTIme` int(11) NOT NULL COMMENT '结束时间',
			  `status` enum('ongoing','pased','done') NOT NULL COMMENT '状态',
			  `remainTime` int(11) NOT NULL COMMENT '剩余时间（秒）',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs

    }
}

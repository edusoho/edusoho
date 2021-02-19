<?php

use Phpmig\Migration\Migration;

class InitItemBank extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `biz_answer_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `answer_scene_id` int(10) unsigned NOT NULL COMMENT '场次id',
                `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                `answer_report_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题报告id',
                `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题者id',
                `begin_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始答题时间',
                `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束答题时间',
                `used_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题时长-秒',
                `status` enum('doing','paused','reviewing','finished') NOT NULL DEFAULT 'doing' COMMENT '答题状态',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                PRIMARY KEY (`id`),
                KEY `answer_scene_id` (`answer_scene_id`),
                KEY `user_id` (`user_id`),
                KEY `answer_scene_id_status` (`answer_scene_id`,`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题记录表';
            
            CREATE TABLE `biz_answer_report` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
                `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                `answer_record_id` int(10) unsigned NOT NULL COMMENT '答题记录id',
                `answer_scene_id` int(10) unsigned NOT NULL COMMENT '场次id',
                `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总得分',
                `right_rate` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '正确率',
                `right_question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答对问题数',
                `objective_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '客观题得分',
                `subjective_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '主观题得分',
                `grade` enum('none', 'excellent','good','passed','unpassed') NOT NULL DEFAULT 'unpassed' COMMENT '等级',
                `comment` text COMMENT '评语',
                `review_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批阅时间',
                `review_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批阅人id',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `answer_record_id` (`answer_record_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题报告';

            CREATE TABLE `biz_answer_question_report` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `identify` varchar(255) NOT NULL COMMENT '唯一标识，(answer_record_id)_(question_id)',
                `answer_record_id` int(10) unsigned NOT NULL COMMENT '答题记录id',
                `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷id',
                `section_id` int(10) unsigned NOT NULL COMMENT '试卷模块id',
                `item_id` int(10) unsigned NOT NULL COMMENT '题目id',
                `question_id` int(10) unsigned NOT NULL COMMENT '问题id',
                `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '得分',
                `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '满分数',
                `response` text,
                `status` enum('reviewing','right','wrong','no_answer', 'part_right') NOT NULL DEFAULT 'reviewing' COMMENT '状态',
                `comment` text COMMENT '评语',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                PRIMARY KEY (`id`),
                KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目问题报告表';

            CREATE TABLE `biz_answer_scene` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '场次名称',
                `limited_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题限制时长(分钟) 0表示不限制',
                `do_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可作答次数 0表示不限制',
                `redo_interval` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题间隔时长(分钟)',
                `need_score` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要计算分值 1表示需要',
                `manual_marking` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否支持手动批阅 1表示支持',
                `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始答题时间 0表示不限制，可作答次数为1时可设置',
                `pass_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '合格分',
                `enable_facein` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否开启云监考',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者id',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新者id',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题场次表';

            CREATE TABLE `biz_assessment` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                `displayable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示 1表示显示',
                `name` varchar(255) NOT NULL COMMENT '试卷名称',
                `description` text COMMENT '试卷说明',
                `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
                `item_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者ID',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新者ID',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `bank_id` (`bank_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷表';

            CREATE TABLE `biz_assessment_section` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷ID',
                `name` varchar(255) NOT NULL COMMENT '名称',
                `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模块顺序',
                `description` text COMMENT '模块说明',
                `item_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                `total_score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '总分',
                `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                `score_rule` varchar(512) NOT NULL DEFAULT '' COMMENT '得分规则',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `assessment_id` (`assessment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷模块表';

            CREATE TABLE `biz_assessment_section_item` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `assessment_id` int(10) unsigned NOT NULL COMMENT '试卷ID',
                `item_id` int(10) unsigned NOT NULL COMMENT '题目ID',
                `section_id` int(10) unsigned NOT NULL COMMENT '模块ID',
                `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
                `score` float(10,1) NOT NULL COMMENT '题目分数',
                `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                `question_scores` text COMMENT '问题分数',
                `score_rule` text COMMENT '得分规则(包括题项)',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `section_id` (`section_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷题目表';

            CREATE TABLE `biz_item` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
                `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
                `material` text COMMENT '题目材料',
                `analysis` text COMMENT '题目解析',
                `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
                `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
                `question_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `bank_id` (`bank_id`),
                KEY `difficulty` (`difficulty`),
                KEY `category_id` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目表';

            CREATE TABLE `biz_item_bank` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(1024) NOT NULL COMMENT '题库名称',
                `assessment_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷数量',
                `item_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库表';

            CREATE TABLE `biz_item_category` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(1024) NOT NULL COMMENT '名称',
                `weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权重',
                `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级分类id',
                `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `bank_id` (`bank_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目分类表';

            CREATE TABLE `biz_question` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '问题ID',
                `item_id` int(10) unsigned NOT NULL COMMENT '题目ID',
                `stem` text COMMENT '题干',
                `seq` int(10) unsigned NOT NULL COMMENT '序号',
                `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
                `answer_mode` varchar(255) NOT NULL DEFAULT '' COMMENT '作答方式',
                `response_points` text COMMENT '答题点信息',
                `answer` text COMMENT '参考答案',
                `analysis` text COMMENT '问题解析',
                `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者',
                `updated_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新更新用户',
                `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `item_id` (`item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题表';
            
            CREATE TABLE IF NOT EXISTS `biz_facein_cheat_record` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
                `answer_scene_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '场次id',
                `answer_record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答题记录id',
                `status` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊状态',
                `level` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊等级',
                `duration` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '',
                `behavior` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '作弊行为',
                `picture_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件路径',
                `created_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `answer_scene_id` (`answer_scene_id`),
                KEY `answer_record_id` (`answer_record_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='云监考作弊记录';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('
            DROP TABLE IF EXISTS `biz_answer_record`;
            DROP TABLE IF EXISTS `biz_answer_report`;
            DROP TABLE IF EXISTS `biz_answer_question_report`;
            DROP TABLE IF EXISTS `biz_answer_scene`;
            DROP TABLE IF EXISTS `biz_assessment`;
            DROP TABLE IF EXISTS `biz_assessment_section`;
            DROP TABLE IF EXISTS `biz_assessment_section_item`;
            DROP TABLE IF EXISTS `biz_item`;
            DROP TABLE IF EXISTS `biz_item_bank`;
            DROP TABLE IF EXISTS `biz_item_category`;
            DROP TABLE IF EXISTS `biz_question`;
            DROP TABLE IF EXISTS `biz_facein_answer_result`;
        ');
    }
}

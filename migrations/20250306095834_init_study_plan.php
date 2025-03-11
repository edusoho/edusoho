<?php

use Phpmig\Migration\Migration;

class InitStudyPlan extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `study_plan` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '计划ID',
              `user_id` INT(11) NOT NULL COMMENT '学员ID',
              `course_id` INT(11) NOT NULL COMMENT '课程ID',
              `start_date` BIGINT NOT NULL COMMENT '计划开始日期',
              `end_date` BIGINT NOT NULL COMMENT '计划截止日期',
              `weekly_days` VARCHAR(20) NOT NULL COMMENT '每周学习日（如1,3,5表示周一、三、五）',
              `total_days` INT(11) NOT NULL COMMENT '总学习天数（自动计算）',
              `daily_avg_time` BIGINT NOT NULL COMMENT '每日平均学习时长（分钟）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_user_id` (`user_id`),
              KEY `idx_course_id` (`course_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划主表';
            CREATE TABLE `study_plan_detail` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
              `plan_id` INT(11) NOT NULL COMMENT '学习计划ID',
              `study_date` BIGINT NOT NULL COMMENT '学习日期',
              `task_names` TEXT NOT NULL COMMENT '当日任务名称（多个用逗号分隔）',
              `total_time` BIGINT NOT NULL COMMENT '当日学习总时长（分钟）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_plan_id` (`plan_id`),
              KEY `idx_study_date` (`study_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划每日任务详情表';
            CREATE TABLE `ai_study_config` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                `is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI伴学服务开启状态 0-关闭 1-开启',
                `major_id` varchar(32) NOT NULL COMMENT '用户选择的专业类型',
                `plan_deadline` INT(10) unsigned NOT NULL COMMENT '学习计划截止时间',
                `is_diagnosis_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI知识点诊断开关 0-关闭 1-开启',
                `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
                `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI伴学服务配置表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `study_plan`;
            DROP TABLE IF EXISTS `study_plan_detail`;
            DROP TABLE IF EXISTS `ai_study_config`;
        ');
    }
}

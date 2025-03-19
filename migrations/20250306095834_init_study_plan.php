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
            CREATE TABLE IF NOT EXISTS `study_plan` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '计划ID',
              `userId` INT(11) NOT NULL COMMENT '学员ID',
              `courseId` INT(11) NOT NULL COMMENT '课程ID',
              `startDate` BIGINT NOT NULL COMMENT '计划开始日期',
              `endDate` BIGINT NOT NULL COMMENT '计划截止日期',
              `weekDays` VARCHAR(20) NOT NULL COMMENT '每周学习日（如1,3,5表示周一、三、五）',
              `totalDays` INT(11) NOT NULL COMMENT '总学习天数（自动计算）',
              `dailyAvgTime` BIGINT NOT NULL COMMENT '每日平均学习时长（分钟）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idxUserId` (`userId`),
              KEY `idxCourseId` (`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划主表';

            CREATE TABLE IF NOT EXISTS `study_plan_detail` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
              `planId` INT(11) NOT NULL COMMENT '学习计划ID',
              `studyDate` BIGINT NOT NULL COMMENT '学习日期',
              `taskNames` TEXT NOT NULL COMMENT '当日任务名称（多个用逗号分隔）',
              `totalTime` BIGINT NOT NULL COMMENT '当日学习总时长（分钟）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idxPlanId` (`planId`),
              KEY `idxStudyDate` (`studyDate`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划每日任务详情表';

            CREATE TABLE IF NOT EXISTS `ai_study_config` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                `courseId` INT(11) NOT NULL COMMENT '课程计划ID',
                `isActive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI伴学服务开启状态 0-关闭 1-开启',
                `datasetId` varchar(256) NOT NULL COMMENT '知识库ID',
                `domainId` varchar(32) NOT NULL COMMENT '用户选择的专业类型',
                `planDeadline` text NOT NULL COMMENT '学习计划截止时间',
                `isDiagnosisActive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI知识点诊断开关 0-关闭 1-开启',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                UNIQUE INDEX `uniqueCourseId`(`courseId`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI伴学服务配置表';
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

<?php

class Lesson2CourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('course_task')) {
            $this->getConnection()->exec(
                "
                CREATE TABLE `course_task` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0',
                  `seq` int(10) unsigned NOT NULL,
                  `categoryId` int(10) DEFAULT NULL,
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引用的教学活动',
                  `title` varchar(255) NOT NULL COMMENT '标题',
                  `isFree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
                  `isOptional` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否必修',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
                  `status` varchar(255) NOT NULL DEFAULT 'create' COMMENT '发布状态 create|publish|unpublish',
                  `createdUserId` int(10) unsigned NOT NULL COMMENT '创建者',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `mode` varchar(60) DEFAULT NULL COMMENT '任务模式',
                  `number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务编号',
                  `type` varchar(50) NOT NULL COMMENT '任务类型',
                  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
                  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
                  `maxOnlineNum` int(11) unsigned DEFAULT '0' COMMENT '任务最大可同时进行的人数，0为不限制',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源task的id',
                  PRIMARY KEY (`id`),
                  KEY `seq` (`seq`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isIndexExist('course_task', 'courseId')) {
            $this->getConnection()->exec("
                ALTER TABLE course_task ADD INDEX courseId (`courseId`);
            ");
        }

        if (!$this->isFieldExist('course_task', 'migrateLessonId')) {
            $this->exec("alter table `course_task` add `migrateLessonId` int(10) default 0;");
        }

        if (!$this->isIndexExist('course_task', 'migrateLessonIdAndType')) {
            $this->exec("alter table `course_task` add index migrateLessonIdAndType (`migrateLessonId`, `type`)");
        }

        if (!$this->isIndexExist('course_task', 'migrateLessonIdAndActivityId')) {
            $this->exec("alter table `course_task` add index migrateLessonIdAndActivityId (`migrateLessonId`, `activityId`)");
        }

        $countSql = 'SELECT count(*) from `course_lesson` WHERE `id` NOT IN (SELECT migrateLessonId FROM `course_task`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->getConnection()->exec(
            "
            insert into course_task(
                 `id`,
                 `courseId`,
                 `fromCourseSetId`,
                 `seq`,
                 `categoryId`,
                 `title`,
                 `isFree`,
                 `startTime`,
                 `endTime`,
                 `status`,
                 `createdUserId`,
                 `createdTime`,
                 `updatedTime`,
                 `mode` ,
                 `number`,
                 `type`,
                 `mediaSource` ,
                 `length` ,
                 `maxOnlineNum`,
                 `copyId`,
                 `migrateLessonId`
            ) select
                `id`,
                `courseId`,
                `courseId`,
                `seq`,
                `chapterId`,
                `title`,
                `free`,
                `startTime`,
                `endTime`,
                `status`,
                `userId`,
                `createdTime`,
                `updatedTime`,
                'lesson',
                `number`,
                `type`,
                `mediaSource`,
                CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
                `maxOnlineNum`,
                `copyId`,
                `id` as `migrateLessonId`
            from `course_lesson` WHERE `id` NOT IN (SELECT id FROM `course_task`) order by id limit 0, {$this->perPageCount}");

        return $page + 1;
    }
}

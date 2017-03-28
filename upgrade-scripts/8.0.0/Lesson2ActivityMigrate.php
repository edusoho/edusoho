<?php

class Lesson2ActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity')) {
            $this->getConnection()->exec(
                "
             CREATE TABLE `activity` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `title` varchar(255) NOT NULL COMMENT '标题',
                  `remark` text,
                  `mediaId` int(10) unsigned DEFAULT '0' COMMENT '教学活动详细信息Id，如：视频id, 教室id',
                  `mediaType` varchar(50) NOT NULL COMMENT '活动类型',
                  `content` text COMMENT '活动描述',
                  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
                  `fromCourseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属教学计划',
                  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属的课程',
                  `fromUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者的ID',
                  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源activity的id',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isFieldExist('activity', 'migrateLessonId')) {
            $this->exec("alter table `activity` add `migrateLessonId` int(10);");
        }

        $countSql = 'SELECT count(*) from `course_lesson` WHERE `id` NOT IN (SELECT migrateLessonId FROM `activity`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->getConnection()->exec(
            "
            insert into `activity`(
                `id`,
                `title` ,
                `remark` ,
                `mediaId` ,
                `mediaType`,
                `content`,
                `length`,
                `fromCourseId`,
                `fromCourseSetId`,
                `fromUserId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`,
                `migrateLessonId`
            )select
                `id`,
                `title`,
                `summary`,
                `mediaId`,
                CASE WHEN `type` = 'document' THEN 'doc'  ELSE TYPE END AS 'type',
                `content`,
                CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
                `courseId`,
                `courseId`,
                `userId`,
                `startTime`,
                `endTime`,
                `createdTime`,
                `updatedTime`,
                `copyId`,
                `id`
            from `course_lesson` where `id` not in (select migrateLessonId from `activity`) order by id limit 0, {$this->perPageCount};
        "
        );

        return $page++;
    }
}

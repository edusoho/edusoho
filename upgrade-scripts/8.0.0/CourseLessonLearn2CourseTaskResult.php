<?php

class CourseLessonLearn2CourseTaskResult extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('course_task_result')) {
            $this->exec(
                "
                CREATE TABLE `course_task_result` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
                  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务进行时长（分钟）',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        if (!$this->isIndexExist('course_task_result', 'courseId_userId')) {
            $this->getConnection()->exec("
                ALTER TABLE course_task_result ADD INDEX courseId_userId (`courseId`,`userId`);
            ");
        }

        if (!$this->isIndexExist('course_task_result', 'courseTaskId_userId')) {
            $this->getConnection()->exec("
                ALTER TABLE course_task_result ADD INDEX courseTaskId_userId (`courseTaskId`,`userId`);
            ");
        }

        $countSql = 'SELECT count(*) FROM `course_lesson_learn` where `id` not in (select `id` from `course_task_result`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            $this->exec("UPDATE `course_task_result` cl,  `course_task` ck SET cl.`activityId`= ck.`activityId` WHERE cl.`courseTaskId` = ck.`id`;");
            return;
        }

        $this->exec(
            "
            insert into `course_task_result`
            (
                `id`,
                `courseId`,
                `courseTaskId`,
                `userId`,
                `status`,
                `finishedTime`,
                `createdTime`,
                `updatedTime`,
                `time`,
                `watchTime`
            )
            select
                `id`,
                `courseId`,
                `lessonId`,
                `userId`,
                case when `status` = 'finished' then 'finish' else 'start' end AS 'status',
                `finishedTime`,
                `updateTime`,
                `updateTime`,
                `learnTime`,
                `watchTime`
            from `course_lesson_learn` where id not in (select id from `course_task_result`)
            order by id limit 0, {$this->perPageCount};
            "
        );

        return $page + 1;
    }
}

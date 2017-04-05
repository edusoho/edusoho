<?php

class ActivityLearnLog extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_learn_log')) {
            $this->exec(
                "
                CREATE TABLE `activity_learn_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `event` varchar(255) NOT NULL DEFAULT '' COMMENT '',
                  `data` text COMMENT '',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `learnedTime` int(11) DEFAULT 0,
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }

        $this->exec(
            "
              insert into activity_learn_log
                (
                  `activityId`,
                  `courseTaskId` ,
                  `userId`,
                  `event`,
                  `watchTime`,
                  `learnedTime` ,
                  `createdTime`
                )
                select
                  ck.`activityId`,
                  ck.`id`,
                  ct.`userId`,
                  CONCAT(ck.`type` ,'.','start'),
                  0,
                  0,
                  ct.createdTime
                 FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ck.id not in (select courseTaskId from activity_learn_log where event like '%.start');
                "
        );


        $this->exec("
            insert into activity_learn_log
             (
               `activityId`,
               `courseTaskId` ,
               `userId`,
               `event`,
               `watchTime`,
               `learnedTime` ,
               `createdTime`
             )
             select
               ck.`activityId`,
               ck.`id`,
               ct.`userId`,
               CONCAT(ck.`type` ,'.','doing'),
               ct.`watchTime`,
               ct.`time`,
               ct.createdTime
              FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ck.id not in (select courseTaskId from activity_learn_log where event like '%.doing');
        ");

        $this->exec("
            insert into activity_learn_log
             (
               `activityId`,
               `courseTaskId` ,
               `userId`,
               `event`,
               `watchTime`,
               `learnedTime` ,
               `createdTime`
             )
             select
               ck.`activityId`,
               ck.`id`,
               ct.`userId`,
               CONCAT(ck.`type` ,'.','stay'),
               ct.`watchTime`,
               ct.`time`,
               ct.createdTime
              FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ck.id not in (select courseTaskId from activity_learn_log where event like '%.stay');
        ");

        $this->exec("
            insert into activity_learn_log
              (
                `activityId`,
                `courseTaskId` ,
                `userId`,
                `event`,
                `watchTime`,
                `learnedTime` ,
                `createdTime`
              )
              select
                ck.`activityId`,
                ck.`id`,
                ct.`userId`,
                CONCAT(ck.`type` ,'.','finish'),
                ct.`watchTime`,
                ct.`time`,
                ct.createdTime
               FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ct.status = 'finish' and ck.id not in (select courseTaskId from activity_learn_log where event like '%.finish');
            ");

    }
}
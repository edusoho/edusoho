<?php

class ActivityLearnLogStart extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('activity_learn_log')) {
            $this->exec(
                "
                CREATE TABLE `activity_learn_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `mediaType` varchar(32) NOT NULL COMMENT '活动类型',
                  `event` varchar(32) NOT NULL COMMENT '事件类型',
                  `data` text,
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `learnedTime` int(11) DEFAULT '0',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `migrateTaskResultId` int(10) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        if(!$this->isFieldExist('activity_learn_log','migrateTaskResultId')){
            $this->exec('alter table `activity_learn_log` add `migrateTaskResultId` int(10);');
        }

        if (!$this->isIndexExist('activity_learn_log', 'activityId_userId')) {
            $this->getConnection()->exec("
                ALTER TABLE activity_learn_log ADD INDEX activityId_userId (`activityId`,`userId`);
            ");
        }

        if (!$this->isIndexExist('activity_learn_log', 'event')) {
            $this->getConnection()->exec("
                ALTER TABLE activity_learn_log ADD INDEX event (`event`);
            ");
        }


        $countSql = "SELECT count(id) FROM `course_task_result` WHERE id NOT IN (SELECT migrateTaskResultId FROM `activity_learn_log` where event = 'start' )";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec(
            "
              insert into activity_learn_log
                (
                  `activityId`,
                  `courseTaskId` ,
                  `userId`,
                  `event`,
                  `mediaType`,
                  `watchTime`,
                  `learnedTime` ,
                  `createdTime`,
                  `migrateTaskResultId`
                )
                select
                  ck.`activityId`,
                  ck.`id`,
                  ct.`userId`,
                  'start',
                  `type`,
                  0,
                  0,
                  ct.createdTime,
                  ct.id
                 FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ck.id not in (select courseTaskId from activity_learn_log where event = 'start')
                 limit 0, {$this->perPageCount};
                "
        );

        return $page+1;
    }
}

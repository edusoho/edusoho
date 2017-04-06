<?php

class ActivityLearnLogDoing extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "SELECT count(ct.id) FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ct.id  NOT IN (SELECT migrateTaskResultId FROM `activity_learn_log` where event = 'doing' )";
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
               'doing',
               `type`,
               ct.`watchTime`,
               ct.`time`,
               ct.createdTime,
               ct.id
              FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ct.id not in (select migrateTaskResultId  from activity_learn_log where event = 'doing')
              limit 0, {$this->perPageCount};
        "
        );

        return $page + 1;
    }
}

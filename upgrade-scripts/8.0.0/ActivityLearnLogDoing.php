<?php

class ActivityLearnLogDoing extends AbstractMigrate
{
    public function update($page)
    {

        $countSql = "SELECT count(id) FROM `course_task_result` WHERE id NOT IN (SELECT migrateTaskResultId FROM `activity_learn_log` where event = 'doing' )";
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
              FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ck.id not in (select courseTaskId from activity_learn_log where event = 'doing')
              limit 0, {$this->perPageCount};
        "
        );

        return $page + 1;
    }
}

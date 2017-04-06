<?php

class ActivityLearnLogFinish extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "SELECT count(ct.id) FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ct.status = 'finish' and ct.id  NOT IN (SELECT migrateTaskResultId FROM `activity_learn_log` where event = 'finish' )";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec("
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
                'finish',
                `type`,
                ct.`watchTime`,
                ct.`time`,
                ct.`createdTime`,
                ct.`id`
               FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` and ct.status = 'finish' and ct.id not in (select migrateTaskResultId  from activity_learn_log where event = 'finish')
               limit 0, {$this->perPageCount};
            ");

        return $page+1;
    }
}

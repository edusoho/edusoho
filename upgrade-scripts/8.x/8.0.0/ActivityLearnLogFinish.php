<?php

class ActivityLearnLogFinish extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "SELECT count(ct.id) FROM  course_task_result ct, course_task ck  WHERE ct.`activityId` = ck.id  and ct.status = 'finish'";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->perPageCount = 100000;
        $start = $this->getStart($page);

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
               FROM course_task_result ct ,course_task ck  WHERE  ct.`activityId` = ck.id   and ct.status = 'finish'
               order by ct.id limit {$start}, {$this->perPageCount};
            ");

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

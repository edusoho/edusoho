<?php

class ActivityLearnLogDoing extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "SELECT count(ct.id) FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId`";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->perPageCount = 100000;
        $start = $this->getStart($page);

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
              FROM course_task ck, course_task_result ct WHERE ck.id = ct.`activityId` 
              order by ct.id limit {$start}, {$this->perPageCount};
        "
        );

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

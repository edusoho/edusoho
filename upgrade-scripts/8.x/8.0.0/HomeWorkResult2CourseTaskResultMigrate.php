<?php

class HomeWorkResult2CourseTaskResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework')) {
            return;
        }

        $countSql = 'SELECT count(*) FROM `homework_result`';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }
        $this->perPageCount = 100000;
        $start = $this->getStart($page);

        $this->exec("
          INSERT INTO `course_task_result`
          (
            `activityId`,
            `courseId`,
            `courseTaskId`,
            `userId`,
            `status`,
            `finishedTime`,
            `createdTime`,
            `updatedTime`
          )
          SELECT
            ct.`activityId`,
            ht.`courseId`,
            ct.`id`,
            ht.`userId`,
            (CASE WHEN ht.status = 'finished' THEN 'finish' ELSE 'start' END ),
            ht.`usedTime`,
            ht.`createdTime`,
            ht.`updatedTime`
          FROM course_task ct, homework_result ht WHERE ct.`migrateHomeworkId` = ht.`homeworkId` ORDER BY ht.id limit {$start}, {$this->perPageCount};
        ");

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

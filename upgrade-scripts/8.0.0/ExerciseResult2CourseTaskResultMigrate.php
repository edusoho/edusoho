<?php

class ExerciseResult2CourseTaskResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise')) {
            return;
        }

        $countSql = 'SELECT count(*) FROM `exercise_result`';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }
        $this->perPageCount = 100000;
        $start = $this->getStart($page);

        $this->exec("
          insert into `course_task_result`
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
              et.`courseId`,
              ct.`id`,
              et.`userId`,
              (case when et.status = 'finished' then 'finish' else 'start' end ),
              et.`usedTime`,
              et.`createdTime`,
              et.`updatedTime`
            FROM course_task ct, exercise_result et WHERE ct.`migrateExerciseId` = et.`exerciseId` order by et.id limit {$start}, {$this->perPageCount};
        ");

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

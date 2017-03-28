<?php

class ExerciseResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise_result')) {
            return;
        }

        $nextPage = $this->insertExerciseResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateExerciseResult();
    }

    private function updateExerciseResult()
    {
        //courseId,courseSetId 跟原来的值相同，只需要改testId和lessonId
        $sql = "UPDATE testpaper_result_v8 AS tr, (SELECT id,migrateTestId FROM testpaper_v8 WHERE type = 'exercise') as tmp set testId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.id";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_result_v8 AS tr, (SELECT id,mediaId FROM activity WHERE mediaType = 'exercise') as tmp set lessonId = tmp.id where tr.type = 'exercise' AND tr.testId = tmp.mediaId";
        $this->getConnection()->exec($sql);
    }

    private function insertExerciseResult($page)
    {
        $countSql = "SELECT count(id) FROM `exercise_result` where `id` not in (select `migrateResultId` from `testpaper_result_v8` where type = 'exercise');";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO testpaper_result_v8 (
                testId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updateTime,
                status,
                usedTime,
                type,
                courseSetId,
                migrateResultId )
            SELECT
                exerciseId,
                userId,
                courseId,
                lessonId,
                rightItemCount,
                updatedTime,
                status,
                usedTime,
                'exercise',
                0,
                id AS migrateResultId
            FROM exercise_result WHERE id NOT IN (SELECT migrateResultId FROM testpaper_result_v8 WHERE type = 'exercise') order by id limit 0, {$this->perPageCount}";
        $this->getConnection()->exec($sql);

        return $page+1;
    }
}

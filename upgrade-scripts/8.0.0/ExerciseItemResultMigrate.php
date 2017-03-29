<?php

class ExerciseItemResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise_item_result')) {
            return;
        }

        $nextPage = $this->insertExerciseItemResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateExerciseItemResult();
    }

    private function updateExerciseItemResult()
    {
        $sql = "UPDATE testpaper_item_result_v8 AS rt,(SELECT id ,migrateTestId FROM testpaper_v8 WHERE type = 'exercise') AS tmp SET
            rt.testId = tmp.id WHERE rt.migrateType = 'exercise' AND tmp.migrateTestId = rt.testId ";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_item_result_v8 AS rt,(SELECT id,migrateResultId FROM testpaper_result_v8 WHERE type = 'exercise') AS tmp SET
            rt.resultId = tmp.id WHERE rt.migrateType = 'exercise' AND tmp.migrateResultId = rt.resultId ";
        $this->getConnection()->exec($sql);
    }

    private function insertExerciseItemResult($page)
    {
        $countSql = "SELECT count(id) FROM `exercise_item_result` where `id` not in (select `migrateItemResultId` from `testpaper_item_result_v8` where migrateType = 'exercise');";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO testpaper_item_result_v8 (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            migrateItemResultId,
            migrateType
        ) SELECT
            exerciseId,
            exerciseResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS migrateItemResultId,
            'exercise' FROM exercise_item_result WHERE id NOT IN (SELECT migrateItemResultId FROM testpaper_item_result_v8 WHERE migrateType = 'exercise') order by id limit 0, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        return $page + 1;
    }
}

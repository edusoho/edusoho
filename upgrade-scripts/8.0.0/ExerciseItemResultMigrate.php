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
            rt.testId = tmp.id WHERE rt.type = 'exercise' AND tmp.migrateTestId = rt.testId ";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_item_result_v8 AS rt,(SELECT id,migrateResultId FROM testpaper_result_v8 WHERE type = 'exercise') AS tmp SET
            rt.resultId = tmp.id WHERE rt.type = 'exercise' AND tmp.migrateResultId = rt.resultId ";
        $this->getConnection()->exec($sql);
    }

    private function insertExerciseItemResult($page)
    {
        $countSql = "SELECT count(id) FROM `exercise_item_result`";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);
        $sql = "INSERT INTO testpaper_item_result_v8 (
            testId,
            resultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            migrateItemResultId,
            type
        ) SELECT
            exerciseId,
            exerciseResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS migrateItemResultId,
            'exercise' FROM exercise_item_result order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

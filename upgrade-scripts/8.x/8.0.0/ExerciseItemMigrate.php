<?php

class ExerciseItemMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise_item')) {
            return;
        }

        $nextPage = $this->insertExerciseItem($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateExerciseItem();
    }

    private function updateExerciseItem()
    {
        $this->getConnection()->exec("
            UPDATE testpaper_item_v8 AS ti,testpaper_v8 AS t SET ti.testId = t.id WHERE ti.testId = t.migrateTestId AND ti.type = 'exercise' AND t.type = 'exercise';
        ");

        $this->getConnection()->exec("
            UPDATE testpaper_item_v8 AS ti,question AS q SET ti.questionType = q.type WHERE ti.questionId = q.id AND ti.type = 'exercise' AND ti.questionType ='';
        ");
    }

    private function insertExerciseItem($page)
    {
        $countSql = "SELECT count(id) FROM `exercise_item`";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);

        $sql = "INSERT INTO testpaper_item_v8 (
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            migrateItemId,
            type
        ) SELECT
            exerciseId,
            seq,
            questionId,
            '',
            parentId,
            score,
            missScore,
            id,
            'exercise' FROM exercise_item 
            order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

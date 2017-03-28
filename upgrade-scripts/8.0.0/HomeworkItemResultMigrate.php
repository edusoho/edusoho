<?php

class HomeworkItemResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework_item_result')) {
            return;
        }

        $nextPage = $this->insertHomeworkItemResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateHomeworkItemResult();
    }

    private function updateHomeworkItemResult()
    {
        $sql = "UPDATE testpaper_item_result_v8 AS rt,(SELECT id,migrateTestId FROM testpaper_v8 WHERE type = 'homework') AS tmp SET rt.testId = tmp.id WHERE rt.type = 'homework' AND rt.testId = tmp.migrateTestId;";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_item_result_v8 AS rt,(SELECT id,migrateResultId FROM testpaper_result_v8 WHERE type = 'homework') AS tmp SET rt.resultId = tmp.id WHERE rt.type = 'homework' AND rt.resultId = tmp.migrateResultId;";
        $this->getConnection()->exec($sql);
    }

    private function insertHomeworkItemResult($page)
    {
        $countSql = "SELECT count(id) FROM `homework_item_result` where `id` not in (select `migrateItemResultId` from `testpaper_item_result_v8` where migrateType = 'homework');";
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
            homeworkId,
            homeworkResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS migrateItemResultId,
            'homework'
            FROM homework_item_result WHERE id NOT IN (SELECT migrateItemResultId FROM testpaper_item_result_v8 where migrateType = 'homework') order by id limit 0, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        return $page++;
    }
}

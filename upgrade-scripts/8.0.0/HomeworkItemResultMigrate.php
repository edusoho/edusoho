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
        if ($page==1) {
            $sql = "delete from testpaper_item_result_v8";
            $this->exec($sql);
        }

        $countSql = "SELECT count(id) FROM `homework_item_result`";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $this->perPageCount = 100000;
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
            homeworkId,
            homeworkResultId,
            userId,
            questionId,
            status,
            answer,
            teacherSay,
            id AS migrateItemResultId,
            'homework'
            FROM homework_item_result 
            order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

<?php

class HomeworkItemMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework_item')) {
            return;
        }

        $nextPage = $this->insertHomeworkItem($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateHomeworkItem();
    }

    private function updateHomeworkItem()
    {
        $this->getConnection()->exec("
            UPDATE testpaper_item_v8 AS ti, testpaper_v8 AS t SET ti.testId = t.id WHERE ti.testId = t.migrateTestId AND ti.type = 'homework' AND t.type = 'homework';
        ");

        $this->getConnection()->exec("
            UPDATE testpaper_item_v8 AS ti, question AS q SET ti.questionType = q.type WHERE ti.questionId = q.id AND ti.type = 'homework' AND ti.questionType ='';
        ");
    }

    private function insertHomeworkItem($page)
    {
        $countSql = "SELECT count(id) FROM `homework_item` ";
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
            homeworkId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            id,
            'homework' FROM homework_item
             order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

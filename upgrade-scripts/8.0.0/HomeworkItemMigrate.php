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
            UPDATE testpaper_item_v8 AS ti, testpaper_v8 AS t SET ti.testId = t.id WHERE ti.testId = t.migrateTestId AND ti.migrateType = 'homework' AND t.type = 'homework';
        ");

        $this->getConnection()->exec("
            UPDATE testpaper_item_v8 AS ti, question AS q SET ti.questionType = q.type WHERE ti.questionId = q.id AND ti.migrateType = 'homework' AND ti.questionType ='';
        ");
    }

    private function insertHomeworkItem($page)
    {
        $countSql = "SELECT count(id) FROM `homework_item` where `id` not in (select `migrateItemId` from `testpaper_item_v8` WHERE migrateType = 'homework')";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO testpaper_item_v8 (
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            migrateItemId,
            migrateType
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
            WHERE id NOT IN (SELECT `migrateItemId` FROM `testpaper_item_v8` WHERE migrateType='homework') order by id limit 0, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        return $page + 1;
    }
}

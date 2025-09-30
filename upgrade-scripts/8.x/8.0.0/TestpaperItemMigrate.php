<?php

class TestpaperItemMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = 'SELECT count(id) FROM `testpaper_item`';
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);
        $sql = "INSERT INTO testpaper_item_v8 (
            id,
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
            id,
            testId,
            seq,
            questionId,
            questionType,
            parentId,
            score,
            missScore,
            id,
            'testpaper' FROM testpaper_item 
            order by id limit {$start}, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}

<?php

class TestpaperItemMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = 'SELECT count(id) FROM `testpaper_item` where `id` not in (select `id` from `testpaper_item_v8`)';
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

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
            migrateType
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
            WHERE id NOT IN (SELECT `id` FROM `testpaper_item_v8`) order by id limit 0, {$this->perPageCount};";
        $this->getConnection()->exec($sql);

        return $page++;
    }
}

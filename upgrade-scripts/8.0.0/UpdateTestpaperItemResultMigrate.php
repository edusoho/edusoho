<?php

class UpdateTestpaperItemResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $sql = "SELECT count(id) FROM `testpaper_item_result_v8` WHERE type = 'testpaper';";
        $count = $this->getConnection()->fetchColumn($sql);
        if ($count > 300000) {
            $nextPage = $this->bigDataUpdate($page,$count);
            if (!empty($nextPage)) {
                return $nextPage;
            }
        } else {
            $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_v8 as t SET ir.testId = t.id WHERE t.migrateTestId = ir.testId and ir.type='testpaper'";
            $this->getConnection()->exec($sql);

            $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_result_v8 AS tr SET ir.resultId = tr.id WHERE tr.migrateResultId = ir.resultId and ir.type='testpaper'";
            $this->getConnection()->exec($sql);
        }
    }

    private function bigDataUpdate($page,$count)
    {
        $start = $this->getStart($page);

        $sql = "SELECT id FROM `testpaper_item_result_v8` WHERE type = 'testpaper' order by id asc limit 1 offset {$start}";
        $startId = $this->getConnection()->fetchColumn($sql);

        $end = $start + $this->perPageCount;
        $sql = "SELECT id FROM `testpaper_item_result_v8` WHERE type = 'testpaper' order by id asc limit 1 offset {$end}";
        $endId = $this->getConnection()->fetchColumn($sql);
        $endId = empty($endId) ? $count : $endId;

        if (!empty($startId)) {
            $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_v8 as t SET ir.testId = t.id WHERE t.migrateTestId = ir.testId and ir.type='testpaper' and ir.id >= {$startId} and ir.id < {$endId};";
            $this->getConnection()->exec($sql);

            $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_result_v8 AS tr SET ir.resultId = tr.id WHERE tr.migrateResultId = ir.resultId and ir.type='testpaper' and ir.id >= {$startId} and ir.id < {$endId};";
            $this->getConnection()->exec($sql);

            return $page + 1;
        }

        return ;
    }
}
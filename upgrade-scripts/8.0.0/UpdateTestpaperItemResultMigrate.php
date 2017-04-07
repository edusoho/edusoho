<?php

class UpdateTestpaperItemResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_v8 as t SET ir.testId = t.id WHERE t.migrateTestId = ir.testId";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_item_result_v8 AS ir, testpaper_result_v8 AS tr SET ir.resultId = tr.id WHERE tr.migrateResultId = ir.resultId";
        $this->getConnection()->exec($sql);
    }
}
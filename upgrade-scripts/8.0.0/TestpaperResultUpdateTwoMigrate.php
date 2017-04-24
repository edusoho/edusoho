<?php

class TestpaperResultUpdateTwoMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->perPageCount = 5000;

        $nextPage = $this->updateTestpaperResult($page);
        /*if (!empty($nextPage)) {
            return $nextPage;
        }*/
    }

    private function updateTestpaperResult($page)
    {
        /*$countSql = "SELECT count(id) FROM testpaper_result_v8 WHERE type = 'testpaper' AND lessonId > 0;";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);*/

        $sql = "UPDATE testpaper_result_v8 AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND ct.type = 'testpaper' AND tr.type='testpaper' AND tr.lessonId > 0";
        $this->getConnection()->exec($sql);

        /*$nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;*/
    }
}

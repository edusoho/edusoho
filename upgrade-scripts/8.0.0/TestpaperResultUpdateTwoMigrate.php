<?php

class TestpaperResultUpdateTwoMigrate extends AbstractMigrate
{
    public function update($page)
    {
        /*$sql = "SELECT count(id) FROM `testpaper_result_v8` WHERE type = 'testpaper';";
        $count = $this->getConnection()->fetchColumn($sql);
        if ($count > 10000) {
            $nextPage = $this->bigDataUpdate($page);
            if (!empty($nextPage)) {
                return $nextPage;
            }
        } else {*/
            $this->updateTestpaperResult($page);
        //}

    }

    private function updateTestpaperResult($page)
    {
        $sql = "UPDATE testpaper_result_v8 AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND ct.type = 'testpaper' AND tr.type='testpaper' AND tr.lessonId > 0";
        $this->getConnection()->exec($sql);
    }

    private function bigDataUpdate($page)
    {
        $start = $this->getStart($page);

        $sql = "SELECT id FROM `testpaper_result_v8` WHERE type = 'testpaper' order by id asc limit 1 offset {$start}";
        $startId = $this->getConnection()->fetchColumn($sql);

        if (empty($startId)) {
            return ;
        }

        $end = $start + $this->perPageCount;
        $sql = "SELECT id FROM `testpaper_result_v8` WHERE type = 'testpaper' order by id asc limit 1 offset {$end}";
        $endId = $this->getConnection()->fetchColumn($sql);
        $endWhere = empty($endId) ? '' : "  and tr.id < {$endId} ";

        $sql = "UPDATE testpaper_result_v8 AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND ct.type = 'testpaper' AND tr.type='testpaper' AND tr.lessonId > 0 and tr.id >= {$startId} {$endWhere} ";

        $this->getConnection()->exec($sql);


        return $page + 1;
    }
}

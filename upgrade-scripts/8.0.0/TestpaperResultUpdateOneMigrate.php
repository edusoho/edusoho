<?php

class TestpaperResultUpdateOneMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "SELECT count(id) FROM testpaper_result_v8 WHERE type = 'testpaper'";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count > 10000) {
            $nextPage = $this->bigDataUpdate($page,$count);
            if (!empty($nextPage)) {
                return $nextPage;
            }
        } else {
            $this->updateTestpaperResult($page);
        }
    }

    private function updateTestpaperResult($page)
    {
        $sql = "UPDATE testpaper_result_v8 as t, course_lesson as cl set 
                t.courseId = cl.courseId,
                t.courseSetId = cl.courseId, 
                t.lessonId = cl.id 
                where cl.type = 'testpaper' and t.type = 'testpaper' and t.testId = cl.mediaId and t.courseId = 0";
        $this->getConnection()->exec($sql);
    }

    private function bigDataUpdate($page, $count)
    {
        $start = $this->getStart($page);

        $sql = "SELECT id FROM testpaper_result_v8 WHERE type = 'testpaper' order by id asc limit 1 offset {$start}";
        $startId = $this->getConnection()->fetchColumn($sql);

        if (empty($startId)) {
            return ;
        }

        $end = $start + $this->perPageCount;
        $sql = "SELECT id FROM testpaper_result_v8 WHERE type = 'testpaper' order by id asc limit 1 offset {$end}";
        $endId = $this->getConnection()->fetchColumn($sql);
        $endWhere = empty($endId) ? '' : " and t.id < {$endId} ";

        $sql = "UPDATE testpaper_result_v8 as t, course_lesson as cl set 
            t.courseId = cl.courseId,
            t.courseSetId = cl.courseId, 
            t.lessonId = cl.id 
            where cl.type = 'testpaper' and t.type = 'testpaper' and t.testId = cl.mediaId and t.courseId = 0 and t.id >= {$startId} {$endWhere} ";

        $this->getConnection()->exec($sql);

        return $page + 1;
    }
}

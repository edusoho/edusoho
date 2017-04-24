<?php

class TestpaperResultUpdateOneMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $nextPage = $this->updateTestpaperResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function updateTestpaperResult($page)
    {
        $countSql = "SELECT count(id) FROM testpaper_result_v8 WHERE type = 'testpaper' AND courseId = 0 AND target != '';";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $start = $this->getStart($page);

        $sql = "SELECT * FROM testpaper_result_v8 WHERE type = 'testpaper' AND courseId = 0 AND target != '' LIMIT {$start}, {$this->perPageCount};";
        $newTestpaperResults = $this->getConnection()->fetchAll($sql);
        foreach ($newTestpaperResults as $testpaperResult) {
            $targetArr = explode('/', $testpaperResult['target']);
            $courseArr = explode('-', $targetArr[0]);
            $lessonArr = explode('-', $targetArr[1]);

            $courseId = (int) $courseArr[1];
            $lessonId = empty($lessonArr[1]) ? 0 : (int) $lessonArr[1];
            $sql = "UPDATE testpaper_result_v8 SET
                courseId = {$courseId},
                courseSetId = {$courseId},
                lessonId = {$lessonId}
                WHERE id = {$testpaperResult['id']}";

            $this->getConnection()->exec($sql);

            $sql = "UPDATE testpaper_result_v8 SET
                courseId = {$courseId},
                courseSetId = {$courseId},
                lessonId = {$lessonId}
                WHERE testId = {$testpaperResult['testId']} AND userId = {$testpaperResult['userId']} AND target = ''";
            $this->getConnection()->exec($sql);
        }

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;

        $sql = "UPDATE testpaper_result_v8 AS tr, course_task as ct SET tr.lessonId = ct.activityId WHERE tr.lessonId = ct.id AND ct.type = 'testpaper' AND tr.type='testpaper'";
        $this->getConnection()->exec($sql);
    }
}

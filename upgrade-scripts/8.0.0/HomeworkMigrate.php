<?php

class HomeworkMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework')) {
            return;
        }

        $nextPage = $this->insertHomework($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function insertHomework($page)
    {
        $sql = "SELECT * FROM homework WHERE id not IN (SELECT migrateTestId FROM testpaper_v8 WHERE type = 'homework') ORDER BY id LIMIT 0, {$this->perPageCount}; ";
        $homeworks = $this->getConnection()->fetchAll($sql);
        if (!$homeworks) {
            return;
        }

        foreach ($homeworks as $homework) {
            $courseSetId = $homework['courseId'];

            $passedCondition = !empty($homework['correctPercent']) ? $homework['correctPercent'] : null;

            $insert = array(
                'name' => '',
                'description' => $homework['description'],
                'courseId' => $homework['courseId'],
                'lessonId' => $homework['lessonId'],
                'limitedTime' => 0,
                'pattern' => 'questionType',
                'target' => '',
                'status' => 'open',
                'score' => 0,
                'passedCondition' => $passedCondition,
                'itemCount' => $homework['itemCount'],
                'createdUserId' => $homework['createdUserId'],
                'createdTime' => $homework['createdTime'],
                'updatedUserId' => $homework['updatedUserId'],
                'updatedTime' => $homework['updatedTime'],
                'metas' => null,
                'copyId' => $homework['copyId'],
                'type' => 'homework',
                'courseSetId' => $courseSetId,
                'migrateTestId' => $homework['id'],
            );

            $this->getConnection()->insert('testpaper_v8', $insert);
            $homeworkId = $this->getConnection()->lastInsertId();
            $homeworkNew = $this->getConnection()->fetchAssoc("SELECT * FROM testpaper_v8 WHERE id={$homeworkId}");

            if ($homework['copyId'] == 0) {
                $subSql = "UPDATE testpaper_v8 SET copyId = {$homeworkNew['id']} WHERE copyId = {$homework['id']} AND type = 'homework'";
                $this->exec($subSql);
            }
        }

        return $page + 1;
    }
}

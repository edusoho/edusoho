<?php

class HomeworkResultMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework_result')) {
            return;
        }

        $nextPage = $this->insertHomeworkResult($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }

        $this->updateHomeworkResult();
    }

    private function updateHomeworkResult()
    {
        $sql = "UPDATE testpaper_result_v8 AS tr,(SELECT id,migrateTestId FROM testpaper_v8 WHERE type ='homework') AS tmp SET testId = tmp.id WHERE tr.type = 'homework' AND tmp.migrateTestId = tr.testId";
        $this->getConnection()->exec($sql);

        $sql = "UPDATE testpaper_result_v8 AS tr,(SELECT id,mediaId FROM activity) AS tmp SET lessonId = tmp.Id WHERE tr.type = 'homework' AND tmp.mediaId = tr.testId";
        $this->getConnection()->exec($sql);
    }

    private function insertHomeworkResult($page)
    {
        $countSql = "SELECT count(id) FROM `homework_result` where `id` not in (select `migrateResultId` from `testpaper_result_v8` where type = 'homework');";
        $count = $this->getConnection()->fetchColumn($countSql);

        if ($count == 0) {
            return;
        }

        $sql = "INSERT INTO testpaper_result_v8 (
                paperName,
                testId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updateTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                type,
                courseSetId,
                migrateResultId
            )SELECT
                '',
                homeworkId,
                userId,
                courseId,
                lessonId,
                teacherSay,
                rightItemCount,
                passedStatus,
                updatedTime,
                status,
                checkTeacherId,
                checkedTime,
                usedTime,
                'homework',
                courseId AS courseSetId,
                id AS migrateResultId FROM homework_result WHERE id NOT IN (SELECT migrateResultId FROM testpaper_result_v8 WHERE type = 'homework') order by id limit 0, {$this->perPageCount}";
        $this->exec($sql);

        return $page++;
    }
}

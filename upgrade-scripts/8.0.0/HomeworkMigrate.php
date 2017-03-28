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

            $insertSql = "INSERT INTO testpaper_v8 (
                name,
                description,
                courseId,
                lessonId,
                limitedTime,
                pattern,
                target,
                status,
                score,
                passedCondition,
                itemCount,
                createdUserId,
                createdTime,
                updatedUserId,
                updatedTime,
                metas,
                copyId,
                type,
                courseSetId,
                migrateTestId
            ) VALUES (
                '',
                '".$homework['description']."',
                {$homework['courseId']},
                {$homework['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$homework['itemCount']},
                {$homework['createdUserId']},
                {$homework['createdTime']},
                {$homework['updatedUserId']},
                {$homework['updatedTime']},
                null,
                {$homework['copyId']},
                'homework',
                {$courseSetId},
                {$homework['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $homeworkId = $this->getConnection()->lastInsertId();
            $homeworkNew = $this->getConnection()->fetchAssoc("SELECT * FROM testpaper_v8 WHERE id={$homeworkId}");

            if ($homework['copyId'] == 0) {
                $subSql = "UPDATE testpaper_v8 SET copyId = {$homeworkNew['id']} WHERE copyId = {$homework['id']} AND type = 'homework'";
                $this->exec($subSql);
            }

            //homework_item
            $itemSql = "SELECT * FROM homework_item WHERE homeworkId = {$homework['id']} AND id NOT IN (SELECT migrateItemId FROM testpaper_item_v8 WHERE migrateType = 'homework' AND testId = {$homework['id']})";
            $items = $this->getConnection()->fetchAll($itemSql);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                $sql = "INSERT INTO testpaper_item_v8 (
                    testId,
                    seq,
                    questionId,
                    questionType,
                    parentId,
                    score,
                    missScore,
                    migrateItemId,
                    migrateType
                ) VALUES (
                    {$homeworkNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '".$item['questionType']."',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']},
                    'homework'
                )";
                $this->getConnection()->exec($sql);
            }
        }

        return $page+1;
    }
}

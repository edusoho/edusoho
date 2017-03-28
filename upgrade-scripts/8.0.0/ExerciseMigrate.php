<?php

class ExerciseMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise')) {
            return;
        }

        $nextPage = $this->insertExercise($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function insertExercise($page)
    {
        $sql = "SELECT * FROM exercise WHERE id NOT IN (SELECT migrateTestId FROM testpaper_v8 WHERE type = 'exercise') ORDER BY id LIMIT 0, {$this->perPageCount}; ";
        $exercises = $this->getConnection()->fetchAll($sql);
        if (!$exercises) {
            return;
        }

        foreach ($exercises as $exercise) {
            $courseSetId = $exercise['courseId'];

            $passedCondition = json_encode(array('type' => 'submit'));
            $metas = null;
            if (!empty($exercise['difficulty'])) {
                $metas['difficulty'] = $exercise['difficulty'];
            }

            if (!empty($exercise['source'])) {
                $metas['range'] = $exercise['source'];
            }

            $metas['questionTypes'] = json_decode($exercise['questionTypeRange']);
            $metas = json_encode($metas);

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
                '',
                {$exercise['courseId']},
                {$exercise['lessonId']},
                0,
                'questionType',
                '',
                'open',
                0,
                '".$passedCondition."',
                {$exercise['itemCount']},
                {$exercise['createdUserId']},
                {$exercise['createdTime']},
                0,
                0,
                '".$metas."',
                {$exercise['copyId']},
                'exercise',
                {$courseSetId},
                {$exercise['id']}
            )";

            $this->getConnection()->exec($insertSql);
            $exerciseId = $this->getConnection()->lastInsertId();

            $exerciseNew = $this->getConnection()->fetchAssoc("SELECT * FROM testpaper_v8 WHERE id={$exerciseId}");

            if ($exercise['copyId'] == 0) {
                $subSql = "UPDATE testpaper_v8 SET copyId = {$exerciseNew['id']} WHERE copyId = {$exercise['id']} AND type = 'exercise'";
                $this->getConnection()->exec($subSql);
            }

            //exercise_item
            $itemSql = "SELECT * FROM exercise_item WHERE exerciseId = {$exercise['id']} AND id NOT IN (SELECT migrateItemId FROM testpaper_item_v8 WHERE migrateType = 'exercise' AND testId = {$exercise['id']})";
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
                ) values (
                    {$exerciseNew['id']},
                    {$item['seq']},
                    {$item['questionId']},
                    '',
                    {$item['parentId']},
                    {$item['score']},
                    {$item['missScore']},
                    {$item['id']},
                    'exercise'
                )";
                $this->getConnection()->exec($sql);
            }
        }

        return $page++;
    }
}

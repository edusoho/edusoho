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

            $insert = array(
                'name' => '',
                'description' => '',
                'courseId' => $exercise['courseId'],
                'lessonId' => $exercise['lessonId'],
                'limitedTime' => 0,
                'pattern' => 'questionType',
                'target' => '',
                'status' => 'open',
                'score' => 0,
                'passedCondition' => $passedCondition,
                'itemCount' => $exercise['itemCount'],
                'createdUserId' => $exercise['createdUserId'],
                'createdTime' => $exercise['createdTime'],
                'updatedUserId' => 0,
                'updatedTime' => 0,
                'metas' => $metas,
                'copyId' => $exercise['copyId'],
                'type' => 'exercise',
                'courseSetId' => $courseSetId,
                'migrateTestId' => $exercise['id'],
            );

            $this->getConnection()->insert('testpaper_v8', $insert);

            $exerciseId = $this->getConnection()->lastInsertId();

            $exerciseNew = $this->getConnection()->fetchAssoc("SELECT * FROM testpaper_v8 WHERE id={$exerciseId}");

            if ($exercise['copyId'] == 0) {
                $subSql = "UPDATE testpaper_v8 SET copyId = {$exerciseNew['id']} WHERE copyId = {$exercise['id']} AND type = 'exercise'";
                $this->getConnection()->exec($subSql);
            }
        }

        return $page + 1;
    }
}

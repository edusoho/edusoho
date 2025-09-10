<?php
// TODO
class UpdateExercise2CourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('exercise')) {
            return;
        }

        $sql = "UPDATE activity AS a, testpaper_v8 AS t SET a.mediaId = t.id WHERE a.migrateExerciseId = t.migrateTestId AND t.type = 'exercise' AND a.mediaType = 'exercise' AND a.migrateExerciseId > 0;";
        $this->getConnection()->exec($sql);

        $this->getConnection()->exec(
            "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id`
           WHERE a.`migrateExerciseId` = ck.`migrateExerciseId` AND  ck.type = 'exercise' AND  ck.`activityId` = 0 AND a.`migrateExerciseId` > 0
          "
        );
    }
}

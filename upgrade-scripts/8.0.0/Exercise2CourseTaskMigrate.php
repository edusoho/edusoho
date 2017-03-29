<?php

class Exercise2CourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        $count = $this->getConnection()->fetchColumn(
          "
          SELECT count(id) FROM exercise WHERE id NOT IN (SELECT migrateExerciseId FROM activity WHERE mediaType='exercise') AND `lessonId`  IN (SELECT id FROM `course_lesson`);
          "
        );

        if (empty($count)) {
            return;
        }

        $this->exerciseToActivity();
        $this->exerciseToCourseTask();

        return $page + 1;
    }

    /**
     * exercise datas convert to  activity
     * TODO datas should read from table tespaper.
     */
    protected function exerciseToActivity()
    {
        $this->getConnection()->exec("
            INSERT INTO `activity`
            (
              `title`,
              `remark` ,
              `mediaId` ,
              `mediaType`,
              `content`,
              `length`,
              `fromCourseId`,
              `fromCourseSetId`,
              `fromUserId`,
              `startTime`,
              `endTime`,
              `createdTime`,
              `updatedTime`,
              `copyId`,
              `migrateExerciseId`
            )
            SELECT
              '练习',
              `summary`,
              `eexerciseId`,
              'exercise',
              `summary`,
              CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
              `courseId`,
              `courseId`,
              `userId`,
              `startTime`,
              `endTime`,
              `createdTime`,
              `updatedTime`,
              `ecopyId`,
              `eexerciseId`
            FROM (SELECT  ee.id AS eexerciseId, ee.`copyId` AS ecopyId , ce.*
                FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid limit 0, {$this->perPageCount}) lesson
            WHERE lesson.eexerciseId NOT IN (SELECT migrateExerciseId FROM activity WHERE migrateExerciseId IS NOT NULL );
        "
        );

        $sql = "UPDATE activity AS a, testpaper_v8 AS t SET a.mediaId = t.id WHERE a.migrateExerciseId = t.migrateTestId AND t.type = 'exercise' AND a.mediaType = 'exercise';";
        $this->getConnection()->exec($sql);
    }

    protected function exerciseToCourseTask()
    {
        $this->getConnection()->exec(
            "insert into course_task
            (
              `courseId`,
              `fromCourseSetId`,
              `categoryId`,
              `seq`,
              `title`,
              `isFree`,
              `startTime`,
              `endTime`,
              `status`,
              `createdUserId`,
              `createdTime`,
              `updatedTime`,
              `mode` ,
              `number`,
              `type`,
              `length` ,
              `maxOnlineNum`,
              `copyId`,
              `migrateExerciseId`,
              `migrateLessonId`
            )
          select
            `courseId`,
            `courseId`,
            `chapterId`,
            `seq`,
            '练习',
            `free`,
            `startTime`,
            `endTime`,
            `status`,
            `userId`,
            `createdTime`,
            `updatedTime`,
            'exercise',
            `number`,
            'exercise',
            CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
            `maxOnlineNum`,
            `copyId`,
            `eexerciseId`,
            `id`
            FROM (SELECT  ee.id AS eexerciseId, ee.`copyId` AS ecopyId , ce.*
              FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid limit 0, {$this->perPageCount}) lesson
                  WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM course_task WHERE exerciseId IS NOT NULL );
          "
        );

        $this->getConnection()->exec(
            "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id`
           WHERE a.`migrateExerciseId` = ck.`migrateExerciseId` AND  ck.type = 'exercise' AND  ck.`activityId` = 0
          "
        );
    }

    protected function migrateTableStructure()
    {
        if (!$this->isFieldExist('activity', 'migrateExerciseId')) {
            $this->getConnection()->exec('alter table `activity` add `migrateExerciseId` int(10) ;');
        }

        if (!$this->isFieldExist('course_task', 'migrateExerciseId')) {
            $this->getConnection()->exec('alter table `course_task` add `migrateExerciseId` int(10) ;');
        }
    }
}

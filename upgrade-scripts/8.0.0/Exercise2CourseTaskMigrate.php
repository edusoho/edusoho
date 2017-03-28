<?php

class Exercise2CourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        $count = $this->getConnection()->fetchColumn('SELECT  count(ee.id) FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid');
        $start = $this->getStart($page);
        if ($count == 0 && $count < $start) {
            return;
        }

        $this->exerciseToActivity($start);
        $this->exerciseToCourseTask($start);

        return $page++;
    }

    /**
     * exercise datas convert to  activity
     * TODO datas should read from table tespaper.
     */
    protected function exerciseToActivity()
    {
        $this->getConnection()->exec(
          "
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
              `exerciseId`
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
          FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid limit {$start}, {$this->perPageCount}) lesson
          WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM activity WHERE exerciseId IS NOT NULL );
        "
      );
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
              `exerciseId`,
              `lessonId`
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
              FROM  course_lesson  ce , exercise ee WHERE ce.id = ee.lessonid limit {$start}, {$this->perPageCount}) lesson
                  WHERE lesson.eexerciseId NOT IN (SELECT exerciseId FROM course_task WHERE exerciseId IS NOT NULL );
          "
        );

        $this->getConnection()->exec(
          "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id`
           WHERE a.`exerciseId` = ck.`exerciseId` AND  ck.type = 'exercise' AND  ck.`activityId` = 0
          "
        );
    }

    protected function migrateTableStructure()
    {
        if (!$this->isFieldExist('activity', 'exerciseId')) {
            $this->getConnection()->exec('alter table `activity` add `exerciseId` int(10) ;');
        }

        if (!$this->isFieldExist('course_task', 'exerciseId')) {
            $this->getConnection()->exec('alter table `course_task` add `exerciseId` int(10) ;');
        }
    }
}

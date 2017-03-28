<?php

class Homework2CourseTasMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        $count = $this->getConnection()->fetchColumn('SELECT  count(ee.id)  FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonId');
        if (empty($count)) {
            return;
        }

        $this->homeworkToActivity($start);
        $this->homeworkToCourseTask($start);

        return $page+1;
    }

    /**
     * exercise datas convert to  activity
     * TODO datas should read from table tespaper.
     */
    protected function homeworkToActivity($start)
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
              `migrateHomeworkId`
          )
          SELECT
              '作业',
              `summary`,
              `hhomeworkId`,
              'homework',
              `summary`,
              case when `length` is null then 0 else `length` end,
              `courseId`,
              `courseId`,
              `userId`,
              `startTime`,
              `endTime`,
              `createdTime`,
              `updatedTime`,
              `ecopyId`,
              `hhomeworkId`
          FROM (SELECT  ee.id AS hhomeworkId, ee.`copyId` AS ecopyId , ce.*
          FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonid limit 0, {$this->perPageCount}) lesson
          WHERE hhomeworkId NOT IN (SELECT homeworkId FROM activity WHERE homeworkId IS NOT NULL );
                  "
        );

        $sql = "UPDATE activity AS a,testpaper_v8 AS t SET a.mediaId = t.id WHERE a.homeworkId = t.migrateTestId AND t.type = 'homework' AND a.type = 'homework';";
        $this->getConnection()->exec($sql);
    }

    protected function homeworkToCourseTask($start)
    {
        $this->exec(
            "
          INSERT INTO course_task
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
              `migrateHomeworkId`,
              `migrateLessonId`
            )
          SELECT
            `courseId`,
            `courseId`,
            `chapterId`,
            `seq`,
            '作业',
            `free`,
            `startTime`,
            `endTime`,
            `status`,
            `userId`,
            `createdTime`,
            `updatedTime`,
            'homework',
            `number`,
            'homework',
            CASE WHEN `length` is null THEN 0  ELSE `length` END AS `length`,
            `maxOnlineNum`,
            `copyId`,
            `hhomeworkId`,
            `id`
            FROM (SELECT  ee.id AS hhomeworkId, ee.`copyId` AS ecopyId , ce.*
              FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonid limit 0, {$this->perPageCount}) lesson
                  WHERE lesson.hhomeworkId NOT IN (SELECT homeworkId FROM course_task WHERE homeworkId IS NOT NULL );
          "
        );

        $this->exec(
<<<<<<< HEAD
            "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id`
        WHERE a.`homeworkId` = ck.`homeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0
=======
          "UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id`
        WHERE a.`migrateHomeworkId` = ck.`migrateHomeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0
>>>>>>> b2daa043eda0cd9bdf03e6f31f65cd30fced8c61
          "
        );
    }

    protected function migrateTableStructure()
    {
        if (!$this->isFieldExist('activity', 'migrateHomeworkId')) {
            $this->exec('alter table `activity` add `migrateHomeworkId` int(10) ;');
        }

        if (!$this->isFieldExist('course_task', 'migrateHomeworkId')) {
            $this->exec('alter table `course_task` add `migrateHomeworkId` int(10) ;');
        }
    }
}

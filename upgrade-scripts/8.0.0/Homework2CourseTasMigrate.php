<?php

class Homework2CourseTasMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->migrateTableStructure();

        $count = $this->getConnection()->fetchColumn("SELECT count(id) FROM homework WHERE id NOT IN (SELECT migrateHomeworkId FROM activity WHERE mediaType='homework') AND   `lessonId`  IN (SELECT id FROM `course_lesson`);");

        if (empty($count)) {
            $sql = "UPDATE activity AS a,testpaper_v8 AS t SET a.mediaId = t.id WHERE a.migrateHomeworkId = t.migrateTestId AND t.type = 'homework' AND a.mediaType = 'homework';";
            $this->getConnection()->exec($sql);


            $this->exec("UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id` WHERE a.`migrateHomeworkId` = ck.`migrateHomeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0");
            return;
        }

        $this->homeworkToActivity();
        $this->homeworkToCourseTask();

        return $page + 1;
    }

    /**
     * exercise datas convert to  activity
     * TODO datas should read from table tespaper.
     */
    protected function homeworkToActivity()
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
              `migrateHomeworkId`,
              `migrateLessonId`
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
              `hhomeworkId`,
              `id`
          FROM (SELECT  ee.id AS hhomeworkId, ee.`copyId` AS ecopyId , ce.*
          FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonid limit 0, {$this->perPageCount}) lesson
          WHERE hhomeworkId NOT IN (SELECT migrateHomeworkId FROM activity WHERE migrateHomeworkId IS NOT NULL );
                  "
        );

        
    }

    protected function homeworkToCourseTask()
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
                  WHERE lesson.hhomeworkId NOT IN (SELECT migrateHomeworkId FROM course_task WHERE migrateHomeworkId IS NOT NULL );
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

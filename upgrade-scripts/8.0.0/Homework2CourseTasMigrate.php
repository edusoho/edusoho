<?php

class Homework2CourseTasMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework')) {
            return;
        }

        $this->migrateTableStructure();

        $count = $this->getConnection()->fetchColumn("SELECT count(id) FROM (select max(id) as id,lessonId from homework group by lessonId) as tmp WHERE id NOT IN (SELECT migrateHomeworkId FROM activity WHERE mediaType='homework') AND `lessonId`  IN (SELECT id FROM `course_lesson`);");

        if (empty($count)) {
            $this->updateHomeworkTask();
            $this->updateHomeworkActivity();
            $this->updateHomeworkLessonId();

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
              CONCAT(`title`,'的作业'),
              `summary`,
              `hhomeworkId`,
              'homework',
              `summary`,
              0,
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
          FROM (SELECT  max(ee.id) AS hhomeworkId, max(ee.`copyId`) AS ecopyId , ce.*
          FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonId group by ee.lessonId limit 0, {$this->perPageCount}) lesson
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
            CONCAT(`title`,'的作业'),
            0,
            `startTime`,
            `endTime`,
            `status`,
            `userId`,
            `createdTime`,
            `updatedTime`,
            'homework',
            `number`,
            'homework',
            0,
            `maxOnlineNum`,
            `ecopyId`,
            `hhomeworkId`,
            `id`
            FROM (SELECT  max(ee.id) AS hhomeworkId, max(ee.`copyId`) AS ecopyId , ce.*
              FROM  course_lesson  ce , homework ee WHERE ce.id = ee.lessonId group by ee.lessonId limit 0, {$this->perPageCount}) lesson
                  WHERE lesson.hhomeworkId NOT IN (SELECT migrateHomeworkId FROM course_task WHERE migrateHomeworkId IS NOT NULL );
          "
        );
    }

    protected function updateHomeworkTask()
    {
        $this->getConnection()->exec("
            UPDATE course_task as a, (SELECT id,migrateHomeworkId from course_task where type = 'homework') AS tmp set a.copyId = tmp.id WHERE tmp.migrateHomeworkId = a.copyId AND a.type = 'homework' AND a.copyId > 0;
        ");
    }

    protected function updateHomeworkActivity()
    {
        $this->getConnection()->exec("
            UPDATE activity as a, (SELECT id,migrateHomeworkId from activity where mediaType = 'homework') AS tmp set a.copyId = tmp.id WHERE tmp.migrateHomeworkId = a.copyId AND a.mediaType = 'homework' AND a.copyId > 0;
        ");
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

    protected function updateHomeworkLessonId()
    {
        $sql = "UPDATE testpaper_v8 AS t, activity AS a SET t.lessonId = a.id WHERE t.lessonId = a.migrateLessonId AND t.type = 'homework';";
        $this->getConnection()->exec($sql);
    }
}

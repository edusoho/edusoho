<?php

class CourseMaterial2ActivityMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
          "
          INSERT INTO activity
          (
            `title`,
            `mediaType`,
            `fromCourseId`,
            `fromCourseSetId`,
            `fromUserId`,
            `createdTime`,
            `updatedTime`,
            `migrateLessonId`
          )
          SELECT
            '下载',
            'download',
            max(`courseId`) AS `courseId`,
            max(`courseSetId`) AS courseSetId,
            max(`userId`) AS userId ,
            max(`createdTime`) AS createdTime,
            max(`createdTime`) AS updatedTime,
            min(`lessonId`) AS lessonId
          FROM course_material WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 
          AND  lessonId NOT IN (SELECT  DISTINCT   (CASE WHEN `migrateLessonId` IS NULL THEN 0 ELSE `migrateLessonId` END) AS lessonId FROM `activity` WHERE mediaType = 'download') GROUP BY  lessonid;
          "
        );
    }
}

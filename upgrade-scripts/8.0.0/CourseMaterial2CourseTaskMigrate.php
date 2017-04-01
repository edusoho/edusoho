<?php

class CourseMaterial2CourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        // refactor: 数据完整性校验，校验同一个lesson下是否全部的资料已经迁移完成
        // $countSql = "SELECT count(id) FROM course_material WHERE source ='coursematerial' AND type = 'course' AND  lessonId > 0 AND lessonId NOT IN (SELECT lessonId FROM `activity_download`)";
        // $count = $this->getConnection()->fetchColumn($countSql);
        // if ($count == 0) {
        //     return;
        // }

        $this->proccessCourseTask();
        $this->processRelations();
    }

    // refactor: id not in问题
    protected function proccessCourseTask()
    {
        $this->exec(
            "
        INSERT INTO `course_task`
        (
          `courseId`,
          `fromCourseSetId`,
          `seq`,
          `categoryId`,
          `title`,
          `status`,
          `createdUserId`,
          `createdTime`,
          `updatedTime`,
          `mode`,
          `number`,
          `type`,
          `migrateLessonId`
        )
        SELECT
          `courseId`,
          `courseId`,
          `seq`,
          `chapterId`,
          '下载' AS title,
          `status`,
          `userId`,
          `createdTime`,
          `updatedTime`,
          'extraClass',
          `number`,
          'download',
          `id`
        FROM  `course_lesson`  WHERE id IN
        (
          SELECT  max(`lessonId`) AS lessonId
          FROM course_material_v8 WHERE source ='coursematerial' AND TYPE = 'course' AND  lessonid >0 GROUP BY  lessonid
        ) AND  id NOT IN (SELECT `migrateLessonId` FROM course_task WHERE  TYPE ='download')
        "
        );
    }

    protected function processRelations()
    {
        $this->exec(
            "
         	UPDATE  `activity` AS ay ,`activity_download` AS dy SET ay.`mediaId`  =  dy.`id`
         WHERE ay.`migrateLessonId`  = dy.`migrateLessonId` AND ay.`mediaType` = 'download' AND  ay.`mediaId` <>  dy.`id`;
        "
        );

        $this->exec(
            "
            UPDATE  `course_task` AS ck ,`activity` AS ay SET ck.`activityId`  =  ay.`id`
            WHERE ck.`migrateLessonId`  = ay.`migrateLessonId` AND ck.`type` = 'download'  AND ay.mediaType= 'download' AND ck.`activityId`  <>  ay.`id`;
        "
        );

        //修复下载活动和资料的的关系
        $this->exec(
            "
          UPDATE `course_material_v8` cm , `activity`  ay SET  cm.lessonId = ay.id WHERE  cm.`lessonId` = ay.`migrateLessonId` AND cm.`source` = 'coursematerial'  AND ay.`mediaType` = 'download' and cm.lessonId > 0;
        ");
    }
}

<?php

class UpdateCourseChapter extends AbstractMigrate
{
    public function update($page)
    {
        $this->getConnection()->exec(
          "
          UPDATE `course_task` c1 , `course_task` c2 SET c1.`categoryId` = c2.`categoryId`, c1.`status`= c2.`status`
          WHERE c1.`mode` <> 'lesson'  AND c2.`mode` = 'lesson' AND  c1.`migrateLessonId` = c2.`migrateLessonId`  AND c1.`categoryId` <> c2.`categoryId`;

          ALTER TABLE `course_chapter` ADD COLUMN copyCourseId int(10) DEFAULT 0;
          update `course_chapter`  left join `course_v8` c on course_chapter.courseId = c.id  set copyCourseId = c.parentId where c.parentId > 0;

          ALTER TABLE `course_chapter` ADD COLUMN refTaskId int(10) DEFAULT 0;
          ALTER TABLE `course_chapter` ADD COLUMN copyTaskId int(10) DEFAULT 0;
          update `course_chapter` left join `course_task` t on course_chapter.id = t.categoryId set refTaskId = t.id , copyTaskId = t.copyId where t.copyId > 0 and t.mode = 'lesson';

          update `course_chapter` left join `course_task` t on course_chapter.copyTaskId = t.id set course_chapter.copyId = t.categoryId where t.mode = 'lesson';

          ALTER TABLE `course_chapter` DROP COLUMN copyCourseId;
          ALTER TABLE `course_chapter` DROP COLUMN refTaskId;
          ALTER TABLE `course_chapter` DROP COLUMN copyTaskId;
          "
        );
    }
}

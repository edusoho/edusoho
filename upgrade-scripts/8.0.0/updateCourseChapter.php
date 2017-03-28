<?php

class updateCourseChapter extends AbstractMigrate
{
    public function update($page)
    {
        $this->getConnection()->exec(
          "
          UPDATE `course_task` c1 , `course_task` c2 SET c1.`categoryId` = c2.`categoryId`
          WHERE c1.mode <> 'lesson'  AND c2.mode = 'lesson' AND  c1.`migrateLessonId` = c2.`migrateLessonId`  AND c1.`categoryId` <> c2.`categoryId`;
          "
        );
    }
}

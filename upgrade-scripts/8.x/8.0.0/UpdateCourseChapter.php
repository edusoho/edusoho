<?php

class UpdateCourseChapter extends AbstractMigrate
{
    public function update($page)
    {
        $this->getConnection()->exec(
          "
          UPDATE `course_task` c1 , `course_task` c2 SET c1.`categoryId` = c2.`categoryId`, c1.`status`= c2.`status`
          WHERE c1.`mode` <> 'lesson'  AND c2.`mode` = 'lesson' AND  c1.`migrateLessonId` = c2.`migrateLessonId`  AND c1.`categoryId` <> c2.`categoryId`;
          "
        );


        if (!$this->isFieldExist('course_chapter', 'migrateCopyCourseId')) {
            $this->exec("ALTER TABLE `course_chapter` ADD COLUMN migrateCopyCourseId int(10) DEFAULT 0;");
        }

        if (!$this->isFieldExist('course_chapter', 'migrateRefTaskId')) {
            $this->exec("ALTER TABLE `course_chapter` ADD COLUMN migrateRefTaskId int(10) DEFAULT 0;");
        }

        if (!$this->isFieldExist('course_chapter', 'mgrateCopyTaskId')) {
            $this->exec("ALTER TABLE `course_chapter` ADD COLUMN mgrateCopyTaskId int(10) DEFAULT 0;");
        }

        $this->getConnection()->exec("
            UPDATE `course_chapter`  LEFT JOIN `course_v8` c ON course_chapter.courseId = c.id  SET migrateCopyCourseId = c.parentId WHERE c.parentId > 0;
            UPDATE `course_chapter` LEFT JOIN `course_task` t ON course_chapter.id = t.categoryId SET migrateRefTaskId = t.id , mgrateCopyTaskId = t.copyId WHERE t.copyId > 0 AND t.mode = 'lesson';

            UPDATE `course_chapter` LEFT JOIN `course_task` t ON course_chapter.mgrateCopyTaskId = t.id SET course_chapter.copyId = t.categoryId WHERE t.mode = 'lesson';
        ");
    }
}

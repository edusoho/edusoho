<?php

class Lesson2CourseChapterMigrate extends AbstractMigrate
{
    public function update($page)
    {

        if (!$this->isFieldExist('course_chapter', 'migrateLessonId')) {
            $this->exec("alter table `course_chapter` add `migrateLessonId` int(10) default 0;");
        }

        $countSql = 'SELECT count(*) from `course_lesson` WHERE `id` NOT IN (SELECT migrateLessonId FROM `course_chapter`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }

        $this->exec("alter table `course_chapter` modify `type` varchar(255) NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元，lesson为课时。';");

        $sql = "insert into course_chapter (
          courseId,
          type,
          parentId,
          number,
          seq,
          title,
          createdTime,
          copyId,
          migrateLessonId
        ) select 
          courseId,
          'lesson',
          chapterId,
          number,
          seq,
          title,
          createdTime,
          0,
          id
        from course_lesson where id not in (select migrateLessonId from course_chapter) order by id limit 0, {$this->perPageCount};";

        $this->exec($sql);
        return $page+1;
    }
}

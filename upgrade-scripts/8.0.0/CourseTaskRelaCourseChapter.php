<?php

class CourseTaskRelaCourseChapter extends AbstractMigrate
{
    public function update($page)
    {
    	$this->exec("update course_task ct set ct.categoryId = (select id from course_chapter where migrateLessonId=ct.migrateLessonId) where migrateLessonId is not null");
    }
}
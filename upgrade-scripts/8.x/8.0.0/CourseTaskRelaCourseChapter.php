<?php

class CourseTaskRelaCourseChapter extends AbstractMigrate
{
    public function update($page)
    {
    	$this->exec(" UPDATE `course_task` ck, `course_chapter` cr SET ck.`categoryId` = cr.id WHERE ck.`migrateLessonId` = cr.`migrateLessonId` AND cr.type = 'lesson' ");
    }
}

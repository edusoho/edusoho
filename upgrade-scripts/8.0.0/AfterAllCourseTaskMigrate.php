<?php

class AfterAllCourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $sql = "UPDATE `c2_course` c set `publishedTaskNum` = (select count(*) from course_lesson where courseId=c.id and status = 'published')";
        $result = $this->getConnection()->exec($sql);
    }
}

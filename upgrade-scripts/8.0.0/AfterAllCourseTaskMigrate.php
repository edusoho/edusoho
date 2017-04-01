<?php

class AfterAllCourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $sql = "UPDATE `course_v8` c set `publishedTaskNum` = (select count(*) from course_task where courseId=c.id and status = 'published')";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `course_task` c set `fromCourseSetId` = `courseId`";
        $result = $this->getConnection()->exec($sql);
    }
}

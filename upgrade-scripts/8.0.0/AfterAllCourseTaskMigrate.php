<?php

class AfterAllCourseTaskMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $sql = "UPDATE `course_v8` c set `publishedTaskNum` = (select count(*) from course_task where courseId=c.id and status = 'published')";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `course_task` c set `fromCourseSetId` = `courseId`";
        $result = $this->getConnection()->exec($sql);


        $sql = "UPDATE `course_v8` c, (select courseId, count(*) as c from course_task group by courseId) co set `taskNum` = co.c where c.id = co.courseId";
        $result = $this->getConnection()->exec($sql);

        $sql = "UPDATE `course_v8` c, (select courseId, count(*) as c from course_task where status='published' group by courseId) co set `taskNum` = co.c where c.id = co.courseId";
        $result = $this->getConnection()->exec($sql);
    }
}

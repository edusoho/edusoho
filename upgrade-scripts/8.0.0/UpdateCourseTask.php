<?php

class UpdateCourseTask extends AbstractMigrate
{
	public function update($page)
	{
		$this->getConnection()->exec(
            "UPDATE `course_task` ct, `course_lesson` cl set
                 ct.`seq` = cl.`seq`,
                 ct.`categoryId` = cl.`chapterId`,
                 ct.`title` = cl.`title`,
                 ct.`isFree` = cl.`free`,
                 ct.`startTime` = cl.`startTime`,
                 ct.`endTime` = cl.`endTime`,
                 ct.`status` = cl.`status`,
                 ct.`updatedTime` = cl.`updatedTime`,
                 ct.`number` = cl.`number`,
                 ct.`mediaSource`  = cl.`mediaSource` ,
                 ct.`length`  = CASE WHEN cl.`length` is null THEN 0  ELSE cl.`length` END,
                 ct.`maxOnlineNum` = cl.`maxOnlineNum`
            WHERE ct.`migrateLessonId` = cl.`id` and ct.`updatedTime` < cl.`updatedTime`
            "
        );
    }
}

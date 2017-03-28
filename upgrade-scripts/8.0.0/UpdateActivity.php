<?php

class UpdateActivity extends AbstractMigrate
{
	public function update($page)
	{
		$this->getConnection()->exec(
            "UPDATE `activity` a, `course_lesson` cl set
                a.`title`  = cl.`title`,
                a.`remark`  = cl.`summary`,
                a.`mediaId`  = cl.`mediaId`,
                a.`content` = cl.`content`,
                a.`length` = CASE WHEN cl.`length` is null THEN 0  ELSE cl.`length` END,
                a.`startTime` = cl.`startTime`,
                a.`endTime` = cl.`endTime`,
                a.`updatedTime` = cl.`updatedTime`
            where a.`migrateLessonId` = cl.`id` and a.`updatedTime` < cl.`updatedTime`"
        );
    }
}
<?php

class ActivityRelaCourseTask extends AbstractMigrate
{
	public function update($page)
	{
		$this->getConnection()->exec(
            "UPDATE `course_task` ck, activity ay SET ck.`activityId` = ay.`id` WHERE ck.`migrateLessonId` = ay.`migrateLessonId` AND  ck.`activityId` = 0;"
        );
	}
}

<?php

class UpdateHomework2CourseTasMigrate extends AbstractMigrate
{
    public function update($page)
    {
    	$sql = "UPDATE activity AS a,testpaper_v8 AS t SET a.mediaId = t.id WHERE a.migrateHomeworkId = t.migrateTestId AND t.type = 'homework' AND a.mediaType = 'homework';";
            $this->getConnection()->exec($sql);


        $this->exec("UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id` WHERE a.`migrateHomeworkId` = ck.`migrateHomeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0");
    }
}
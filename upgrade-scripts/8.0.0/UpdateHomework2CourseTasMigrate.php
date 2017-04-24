<?php

class UpdateHomework2CourseTasMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isTableExist('homework')) {
            return;
        }

        $sql = "UPDATE activity AS a,testpaper_v8 AS t SET a.mediaId = t.id WHERE a.migrateHomeworkId = t.migrateTestId AND t.type = 'homework' AND a.mediaType = 'homework' AND a.migrateHomeworkId > 0;";
        $this->getConnection()->exec($sql);

        $this->exec("UPDATE `course_task` AS ck, activity AS a SET ck.`activityId` = a.`id` WHERE a.`migrateHomeworkId` = ck.`migrateHomeworkId` AND  ck.type = 'homework' AND  ck.`activityId` = 0 AND a.`migrateHomeworkId` > 0");
    }
}

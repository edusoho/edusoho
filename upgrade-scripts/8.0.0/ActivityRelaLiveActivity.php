<?php

class ActivityRelaLiveActivity extends AbstractMigrate
{
    public function update($page)
    {
        $sql = 'UPDATE `activity_live` SET roomCreated = 1 WHERE liveId > 0;';
        $this->getConnection()->exec($sql);

        /*$sql = "UPDATE activity_live la, (SELECT globalId, lessonId FROM course_lesson_replay where globalId<>'' and globalId is not null) clr set la.`mediaId` = clr.`globalId` WHERE la.`migrateLessonId` = clr.`lessonId`";
        $this->getConnection()->exec($sql);*/

        $this->getConnection()->exec("
            UPDATE  `activity` AS ay ,`activity_live` AS vy SET ay.`mediaId`  =  vy.`id`
            	WHERE ay.`migrateLessonId`  = vy.`migrateLessonId` AND ay.`mediaType` = 'live';
        ");
    }
}

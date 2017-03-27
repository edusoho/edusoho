<?php 

class ActivityRelaLiveActivity extends AbstractMigrate
{
    public function update($page)
    {

		$sql = 'UPDATE `live_activity` SET roomCreated = 1 WHERE liveId > 0;';
        $this->getConnection()->exec($sql);

        $sql = "UPDATE live_activity la, (SELECT globalId, lessonId FROM course_lesson_replay where globalId<>'' and globalId is not null) clr set la.`mediaId` = clr.`globalId` WHERE la.`migrateLessonId` = clr.`lessonId`";
        $this->getConnection()->exec($sql);

        $this->getConnection()->exec("
            UPDATE  `activity` AS ay ,`live_activity` AS vy SET ay.`mediaId`  =  vy.`id`
            	WHERE ay.`migrateLessonId`  = vy.`migrateLessonId` AND ay.`mediaType` = 'text';
        ");
    }
}
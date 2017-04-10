<?php

class ActivityRelaVideoActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->getConnection()->exec(
        "
            UPDATE  `activity` AS ay ,`activity_video` AS vy SET ay.`mediaId`  =  vy.`id`
            	WHERE ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'video';
        "
        );
    }
}
<?php

class ActivityRelaVideoAcitivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->getConnection()->exec(
        "
            UPDATE  `activity` AS ay ,`video_activity` AS vy SET ay.`mediaId`  =  vy.`id`
            	WHERE ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'video';
        "
        );
    }
}
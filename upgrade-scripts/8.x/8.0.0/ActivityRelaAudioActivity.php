<?php 

class ActivityRelaAudioActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->exec("UPDATE  `activity` AS ay ,`activity_audio` AS ty SET ay.`mediaId`  =  ty.`id` WHERE ay.`id`  = ty.`migrateLessonId`   AND ay.`mediaType` = 'audio';"
        );
	}
}

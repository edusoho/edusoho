<?php


class ActivityRelaFlashActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->exec(
			"
	          UPDATE  `activity` AS ay ,`flash_activity` AS ty SET ay.`mediaId`  =  ty.`id`
	          WHERE ay.`id` = ty.`migrateLessonId` AND ay.`mediaType` = 'flash';
			"
        );
    }
}

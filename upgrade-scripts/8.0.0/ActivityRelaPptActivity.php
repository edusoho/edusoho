<?php 

class ActivityRelaPptActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->exec(
			"
	          UPDATE  `activity` AS ay ,`activity_ppt` AS ty SET ay.`mediaId`  =  ty.`id`
	          WHERE ay.`id`  = ty.`migrateLessonId` AND ay.`mediaType` = 'ppt';
			"
        );
    }
}

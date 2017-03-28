<?php 

class ActivityRelaPptActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->exec(
			"
	          UPDATE  `activity` AS ay ,`ppt_activity` AS ty SET ay.`mediaId`  =  ty.`id`
	          WHERE ay.`id`  = ty.`migrateLessonId` AND ay.`mediaType` = 'ppt';
			"
        );
    }
}

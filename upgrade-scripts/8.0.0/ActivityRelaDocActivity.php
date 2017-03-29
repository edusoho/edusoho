<?php 

class ActivityRelaDocActivity extends AbstractMigrate
{
    public function update($page)
    {
    	$this->exec(
			"
	          UPDATE `activity` AS ay, `activity_doc` AS ty SET ay.`mediaId`  =  ty.`id`
	          WHERE ay.`id` = ty.`migrateLessonId` AND ay.`mediaType` = 'doc';
	        "
        );
    }
}
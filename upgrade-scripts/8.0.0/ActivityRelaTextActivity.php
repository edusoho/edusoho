
<?php

class ActivityRelaTextActivity extends AbstractMigrate
{
    public function update($page)
    {
		$this->getConnection()->exec(
        "
            UPDATE  `activity` AS ay ,`activity_text` AS vy SET ay.`mediaId`  =  vy.`id`
            	WHERE ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'text';
        "
        );
    }
}
<?php

class ActivityRelaVideoActivity extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = "select count(id) from `activity`  where `mediaType` = 'video' and mediaid = 0";

        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }
        $this->perPageCount = 20000;
        $start = $this->getStart($page);

		$this->getConnection()->exec(
        "
        UPDATE  `activity` AS ay,
            (select * from  `activity_video` AS vy  order by id limit {$start}, {$this->perPageCount})AS vy 
        SET ay.`mediaId`  =  vy.`id`       
        where  ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'video' and vy.`migrateLessonId` >0
        "
        );
        return $page + 1;
    }
}
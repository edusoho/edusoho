<?php

namespace Custom\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Course\Dao\CustomLessonDao;

//use Topxia\Service\Course\Dao\Impl\LessonDaoImpl as BaseLessonDaoImpl;

class CustomLessonDaoImpl extends BaseDao implements CustomLessonDao
{
    protected $table = 'course_lesson';

    public function findRecentLiveLesson($count)
    {
        $time = time();
        $sql  = "SELECT id, recentTime,courseId,startTime,endTime,status FROM
               (SELECT id, ABS({$time}-startTime) AS recentTime,courseId,startTime,endTime,(startTime>{$time}) AS status FROM {$this->table} WHERE type='live' AND status='published' AND startTime<={$time}
                UNION SELECT id, ABS(startTime-{$time}) AS recentTime,courseId,startTime,endTime,(startTime>{$time}) AS status FROM {$this->table} WHERE type='live' AND status='published' AND startTime>={$time})
                recent ORDER BY status DESC, recentTime ASC LIMIT {$count}";

        return $this->getConnection()->fetchAll($sql);
    }

}

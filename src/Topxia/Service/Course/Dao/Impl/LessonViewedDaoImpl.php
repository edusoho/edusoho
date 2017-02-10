<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonViewedDao;

class LessonViewedDaoImpl extends BaseDao implements LessonViewedDao
{
    protected $table = 'course_lesson_viewed';

    public function deleteViewedsByCourseId($courseId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($courseId));
    }

}
<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseChapterDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseChapterDaoImpl extends GeneralDaoImpl implements CourseChapterDao
{
    protected $table = 'course_chapter';

    public function findChaptersByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ORDER BY createdTime ASC";
        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function declares()
    {
    }
}
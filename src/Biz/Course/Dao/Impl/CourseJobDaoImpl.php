<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseJobDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseJobDaoImpl extends GeneralDaoImpl implements CourseJobDao
{
    protected $table = 'course_job';

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json',
            ),
        );
    }

    public function getByTypeAndCourseId($type, $courseId)
    {
        return $this->getByFields(array('type' => $type, 'courseId' => $courseId));
    }

    public function findByType($type)
    {
        return $this->findByFields(array('type' => $type));
    }

    public function deleteByTypeAndCourseId($type, $courseId)
    {
        return $this->db()->delete($this->table, array('type' => $type, 'courseId' => $courseId));
    }
}

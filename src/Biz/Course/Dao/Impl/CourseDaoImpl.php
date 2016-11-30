<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseDaoImpl extends GeneralDaoImpl implements CourseDao
{
    protected $table = 'c2_course';

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->findInField('courseSetId', array($courseSetId));
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getByFields(array('courseSetId' => $courseSetId, 'isDefault' => 1));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'goals'     => 'delimiter',
                'audiences' => 'delimiter'
            )
        );
    }
}

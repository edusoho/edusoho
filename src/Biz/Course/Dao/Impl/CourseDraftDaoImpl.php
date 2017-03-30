<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDraftDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseDraftDaoImpl extends GeneralDaoImpl implements CourseDraftDao
{
    protected $table = 'course_draft';

    public function getByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId)
    {
        return $this->getByFields(array('courseId' => $courseId, 'activityId' => $activityId, 'userId' => $userId));
    }

    public function deleteCourseDrafts($courseId, $activityId, $userId)
    {
        return $this->db()->delete($this->table(),
            array('courseId' => $courseId, 'activityId' => $activityId, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'courseId = :courseId',
            ),
        );
    }
}

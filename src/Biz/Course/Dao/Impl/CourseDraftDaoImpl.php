<?php

namespace Biz\Course\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseDraftDaoImpl extends GeneralDaoImpl implements CourseDraftDao
{
    protected $table = 'course_draft';

    public function findCourseDraft($courseId, $lessonId, $userId)
    {
        return $this->findByFields(array('courseId' => $courseId, 'lessonId' => $lessonId, 'userId' => $userId));
    }

    public function deleteCourseDrafts($courseId, $lessonId, $userId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId, 'lessonId' => $lessonId, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'orderbys'   => array('createdTime'),
            'conditions' => array(
                'courseId = :courseId'
            )
        );
    }
}

<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseNoteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseNoteDaoImpl extends GeneralDaoImpl implements CourseNoteDao
{
    protected $table = 'course_note';

    public function getByUserIdAndTaskId($userId, $taskId)
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'taskId' => $taskId,
        ));
    }

    public function findByUserIdAndStatus($userId, $status)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? and status = ? ORDER BY `createdTime` DESC ";

        return $this->db()->fetchAll($sql, array($userId, $status)) ?: array();
    }

    public function findByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? and courseId = ? ORDER BY `createdTime` DESC ";

        return $this->db()->fetchAll($sql, array($userId, $courseId)) ?: array();
    }

    public function countByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE userId = ? AND courseId = ?";

        return $this->db()->fetchColumn($sql, array($userId, $courseId));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime', 'updatedTime', 'likeNum'),
            'conditions' => array(
                'id IN (:ids)',
                'courseId = :courseId',
                'userId = :userId',
                'taskId = :taskId',
                'createdTime < :startTimeLessThan',
                'createdTime >= :startTimeGreaterThan',
                'content LIKE :content',
                'courseId IN (:courseIds)',
                'courseSetId IN (:courseSetIds)',
                'courseSetId = :courseSetId',
                'status = :status',
            ),
        );
    }
}

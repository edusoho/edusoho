<?php

namespace Biz\Assistant\Dao\Impl;

use Biz\Assistant\Dao\AssistantStudentDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AssistantStudentDaoImpl extends AdvancedDaoImpl implements AssistantStudentDao
{
    protected $table = 'assistant_student';

    public function countMultiClassGroupStudent($multiClassId)
    {
        $sql = "SELECT assistantId, count(id) as 'studentNum' FROM {$this->table} WHERE multiClassId = ? GROUP BY assistantId";

        return $this->db()->fetchAll($sql, [$multiClassId]) ?: [];
    }

    public function getByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getByFields(['studentId' => $studentId, 'courseId' => $courseId]);
    }

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId)
    {
        return $this->getByFields(['studentId' => $studentId, 'multiClassId' => $multiClassId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime'],
            'conditions' => [
                'courseId = :courseId',
                'assistantId = :assistantId',
                'multiClassId = :multiClassId',
                'assistantId IN (:assistantIds)',
            ],
        ];
    }
}

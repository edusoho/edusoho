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

    public function countMultiClassGroupStudentByGroupIds($multiClassId, $groupIds)
    {
        if (empty($groupIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($groupIds) - 1).'?';
        $sql = "SELECT group_id as groupId, count(id) as 'studentNum' FROM {$this->table} WHERE multiClassId = ? AND group_id IN ({$marks}) GROUP BY group_id";

        return $this->db()->fetchAll($sql, array_merge([$multiClassId], $groupIds)) ?: [];
    }

    public function getByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getByFields(['studentId' => $studentId, 'courseId' => $courseId]);
    }

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId)
    {
        return $this->getByFields(['studentId' => $studentId, 'multiClassId' => $multiClassId]);
    }

    public function findByStudentIdsAndMultiClassId($studentIds, $multiClassId)
    {
        if (empty($studentIds)) {
            return [];
        }
        $marks = str_repeat('?,', count($studentIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE studentId IN ({$marks}) AND multiClassId = ?;";

        return $this->db()->fetchAll($sql, array_merge($studentIds, [$multiClassId]));
    }

    public function findByAssistantIdAndCourseId($assistantId, $courseId)
    {
        return $this->findByFields(['assistantId' => $assistantId, 'courseId' => $courseId]);
    }

    public function deleteByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->db()->delete($this->table(), ['studentId' => $studentId, 'courseId' => $courseId]);
    }

    public function findByMultiClassId($multiClassId)
    {
        return $this->findByFields(['multiClassId' => $multiClassId]);
    }

    public function findByMultiClassIdAndGroupId($multiClassId, $groupId)
    {
        return $this->findByFields(['multiClassId' => $multiClassId, 'group_id' => $groupId]);
    }

    public function findByMultiClassIdAndStudentIds($multiClassId, $studentIds)
    {
        if (empty($studentIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($studentIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE multiClassId = ? AND studentId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge([$multiClassId], $studentIds)) ?: [];
    }

    public function findByMultiClassIds($multiClassIds)
    {
        if (empty($multiClassIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($multiClassIds) - 1).'?';
        $sql = "SELECT *,concat(multiClassId, '_', studentId) as unitKey FROM {$this->table()} WHERE multiClassId IN ({$marks})";

        return $this->db()->fetchAll($sql, $multiClassIds) ?: [];
    }

    public function updateMultiClassStudentsGroup($multiClassId, $conditions)
    {
        if (empty($conditions['studentIds'])) {
            return [];
        }

        $marks = str_repeat('?,', count($conditions['studentIds']) - 1).'?';
        $sql = "UPDATE {$this->table} set group_id = ? WHERE multiClassId = ? AND studentId IN ({$marks})";

        $this->db()->executeQuery($sql, array_merge([$conditions['groupId'], $multiClassId], $conditions['studentIds']));
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
                'studentId IN (:studentIds)',
                'group_id = :group_id',
            ],
        ];
    }
}

<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassGroupDaoImpl extends AdvancedDaoImpl implements MultiClassGroupDao
{
    protected $table = 'multi_class_group';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findGroupsByMultiClassId($multiClassId)
    {
        return $this->findByFields([
            'multi_class_id' => $multiClassId,
        ]);
    }

    public function findGroupsByMultiClassIdAndAssistantIds($multiClassId, $assistantIds)
    {
        if (empty($multiClassId) || empty($assistantIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($assistantIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE multi_class_id = ? AND assistant_id in ($marks);";

        return $this->db()->fetchAll($sql, array_merge([$multiClassId], $assistantIds));
    }

    public function countMultiClassGroupAssistant($multiClassId)
    {
        $sql = "SELECT assistant_id, count(id) as 'groupNum' FROM {$this->table} WHERE multi_class_id = ? GROUP BY assistant_id";

        return $this->db()->fetchAll($sql, [$multiClassId]) ?: [];
    }

    public function findUnAssignGroups($multiClassId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE multi_class_id = ? AND assistant_id = 0";

        return $this->db()->fetchAll($sql, [$multiClassId]) ?: [];
    }

    public function getLatestGroup($multiClassId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE multi_class_id = ? order by `id` desc limit 1";

        return $this->db()->fetchAssoc($sql, [$multiClassId]) ?: [];
    }

    public function getNoFullGroup($multiClassId, $fullNum)
    {
        $sql = "SELECT * FROM {$this->table} WHERE multi_class_id = ? AND student_num < ? order by `id` limit 1";

        return $this->db()->fetchAssoc($sql, [$multiClassId, $fullNum]) ?: [];
    }

    public function findByCourseId($courseId)
    {
        return $this->findByFields([
            'course_id' => $courseId,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
                'id in (:ids)',
                'multi_class_id = :multiClassId',
            ],
        ];
    }
}

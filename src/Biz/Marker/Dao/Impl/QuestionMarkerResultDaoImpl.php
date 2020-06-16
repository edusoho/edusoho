<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\QuestionMarkerResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionMarkerResultDaoImpl extends GeneralDaoImpl implements QuestionMarkerResultDao
{
    protected $table = 'question_marker_result';

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->db()->delete($this->table, ['questionMarkerId' => $questionMarkerId]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->findByFields(['userId' => $userId, 'markerId' => $markerId]);
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->findByFields(['userId' => $userId, 'questionMarkerId' => $questionMarkerId]);
    }

    public function countDistinctUserIdByQuestionMarkerIdAndTaskId($questionMarkerId, $taskId)
    {
        $sql = "SELECT COUNT(DISTINCT(userId)) FROM {$this->table} WHERE questionMarkerId = ? AND taskId = ?";

        return $this->db()->fetchColumn($sql, [$questionMarkerId, $taskId]) ?: 0;
    }

    public function countDistinctUserIdByTaskId($taskId)
    {
        $sql = "SELECT COUNT(DISTINCT(userId)) FROM {$this->table} WHERE taskId = ?";

        return $this->db()->fetchColumn($sql, [$taskId]) ?: 0;
    }

    public function findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId)
    {
        return $this->findByFields(['taskId' => $taskId, 'questionMarkerId' => $questionMarkerId]);
    }

    public function findByUserIdAndMarkerIds($userId, $markerIds)
    {
        if (empty($markerIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($markerIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND markerId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$userId], $markerIds));
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'serializes' => [
                'answer' => 'json',
            ],
            'conditions' => [
                'userId = :userId',
                'markerId = :markerId',
                'status = :status',
                'taskId = :taskId',
                'questionMarkerId = :questionMarkerId',
            ],
        ];
    }
}

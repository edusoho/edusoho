<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\QuestionMarkerResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionMarkerResultDaoImpl extends GeneralDaoImpl implements QuestionMarkerResultDao
{
    protected $table = 'question_marker_result';

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->db()->delete($this->table, array('questionMarkerId' => $questionMarkerId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->findByFields(array('userId' => $userId, 'markerId' => $markerId));
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->findByFields(array('userId' => $userId, 'questionMarkerId' => $questionMarkerId));
    }

    public function countDistinctUserIdByQuestionMarkerIdAndTaskId($questionMarkerId, $taskId)
    {
        $sql = "SELECT COUNT(DISTINCT(userId)) FROM {$this->table} WHERE questionMarkerId = ? AND taskId = ?";

        return $this->db()->fetchColumn($sql, array($questionMarkerId, $taskId)) ?: 0;
    }

    public function countDistinctUserIdByTaskId($taskId)
    {
        $sql = "SELECT COUNT(DISTINCT(userId)) FROM {$this->table} WHERE taskId = ?";

        return $this->db()->fetchColumn($sql, array($taskId)) ?: 0;
    }

    public function findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId)
    {
        return $this->findByFields(array('taskId' => $taskId, 'questionMarkerId' => $questionMarkerId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'serializes' => array(
                'answer' => 'json',
            ),
            'conditions' => array(
                'userId = :userId',
                'markerId = :markerId',
                'status = :status',
                'taskId = :taskId',
                'questionMarkerId = :questionMarkerId',
            ),
        );
    }
}

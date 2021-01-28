<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\QuestionMarkerDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionMarkerDaoImpl extends GeneralDaoImpl implements QuestionMarkerDao
{
    protected $table = 'question_marker';

    public function getMaxSeqByMarkerId($id)
    {
        $sql = "SELECT max(seq) seq FROM {$this->table} WHERE markerId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($id));
    }

    public function merge($sourceMarkerId, $targetMarkerId, $maxSeq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq + ?, markerId = ? WHERE markerId = ? ";

        return $this->db()->executeQuery($sql, array($maxSeq, $targetMarkerId, $sourceMarkerId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByMarkerId($markerId)
    {
        $sql = "SELECT * FROM {$this->table} where markerId = ? order by seq asc";

        return $this->db()->fetchAll($sql, array($markerId));
    }

    public function findByMarkerIds($markerIds)
    {
        if (empty($markerIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($markerIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} where markerId IN ({$marks}) order by markerId asc, seq asc";

        return $this->db()->fetchAll($sql, $markerIds);
    }

    public function findByQuestionId($questionId)
    {
        return $this->findByFields(array('questionId' => $questionId));
    }

    public function waveSeqBehind($markerId, $seq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq + 1 WHERE markerId = ? AND seq >= ? ";

        return $this->db()->executeQuery($sql, array($markerId, $seq));
    }

    public function waveSeqForward($markerId, $seq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq - 1 WHERE markerId = ? AND seq >= ? ";

        return $this->db()->executeQuery($sql, array($markerId, $seq));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime', 'seq'),
            'serializes' => array(
                'answer' => 'json',
                'metas' => 'json',
            ),
            'conditions' => array(
                'id IN ( :ids )',
                'seq = :seq',
                'markerId = :markerId',
                'questionId = :questionId',
                'difficulty = :difficulty',
                'type = :type',
                'stem LIKE :stem',
            ),
        );
    }
}

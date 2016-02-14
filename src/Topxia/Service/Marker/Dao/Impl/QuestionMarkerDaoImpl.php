<?php

namespace Topxia\Service\Marker\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Marker\Dao\QuestionMarkerDao;

class QuestionMarkerDaoImpl extends BaseDao implements QuestionMarkerDao
{
    protected $table = 'question_marker';

    private $serializeFields = array(
        'answer' => 'json',
        'metas'  => 'json'
    );

    public function getQuestionMarker($id)
    {
        $sql            = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $questionMarker = $this->getConnection()->fetchAssoc($sql, array($id));
        return $questionMarker ? $this->createSerializer()->unserialize($questionMarker, $this->serializeFields) : null;
    }

    public function getMaxSeqByMarkerId($id)
    {
        $sql = "SELECT max(seq) seq FROM {$this->table} WHERE markerId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function merge($sourceMarkerId, $targetMarkerId, $maxSeq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq + {$maxSeq}, markerId = {$targetMarkerId} WHERE markerId = ? ";
        return $this->getConnection()->executeQuery($sql, array($sourceMarkerId));
    }

    public function findQuestionMarkersByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks           = str_repeat('?,', count($ids) - 1).'?';
        $sql             = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $questionMarkers = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($questionMarkers, $this->serializeFields);
    }

    public function findQuestionMarkersByMarkerId($markerId)
    {
        $sql             = "SELECT * FROM {$this->table} where markerId = ? order by seq asc";
        $questionMarkers = $this->getConnection()->fetchAll($sql, array($markerId));
        return $this->createSerializer()->unserializes($questionMarkers, $this->serializeFields);
    }

    public function findQuestionMarkersByMarkerIds($markerIds)
    {
        if (empty($markerIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($markerIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} where markerId IN ({$marks}) order by markerId asc, seq asc";
        return $this->getConnection()->fetchAll($sql, $markerIds);
    }

    public function findQuestionMarkersByQuestionId($questionId)
    {
        $sql             = "SELECT * FROM {$this->table} where questionId = ?";
        $questionMarkers = $this->getConnection()->fetchAll($sql, array($questionId));
        return $this->createSerializer()->unserializes($questionMarkers, $this->serializeFields);
    }

    public function searchQuestionMarkersCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addQuestionMarker($questionMarker)
    {
        $questionMarker = $this->createSerializer()->serialize($questionMarker, $this->serializeFields);
        $affected       = $this->getConnection()->insert($this->table, $questionMarker);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert questionMarker error.');
        }

        return $this->getQuestionMarker($this->getConnection()->lastInsertId());
    }

    public function updateQuestionMarkersSeqBehind($markerId, $seq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq + 1 WHERE markerId = ? AND seq >= ? ";
        return $this->getConnection()->executeQuery($sql, array($markerId, $seq));
    }

    public function updateQuestionMarkersSeqForward($markerId, $seq)
    {
        $sql = "UPDATE {$this->table} SET seq = seq - 1 WHERE markerId = ? AND seq >= ? ";
        return $this->getConnection()->executeQuery($sql, array($markerId, $seq));
    }

    public function updateQuestionMarker($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuestionMarker($id);
    }

    public function deleteQuestionMarker($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function searchQuestionMarkers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime', 'seq'));

        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit)
                        ->orderBy($orderBy[0], $orderBy[1]);
        $questionMarkers = $builder->execute()->fetchAll() ?: array();

        return $this->createSerializer()->unserializes($questionMarkers, $this->serializeFields);
    }

    public function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || is_null($value)) {
                return false;
            }

            return true;
        }

        );

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'question_marker')
                        ->andWhere("id IN ( :ids )")
                        ->andWhere('seq = :seq')
                        ->andWhere('markerId = :markerId')
                        ->andWhere('questionId = :questionId')
                        ->andWhere('difficulty = :difficulty')
                        ->andWhere('type = :type')
                        ->andWhere('stem LIKE :stem');

        return $builder;
    }
}

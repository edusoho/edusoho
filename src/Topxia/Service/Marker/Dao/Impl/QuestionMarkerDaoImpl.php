<?php

namespace Topxia\Service\Marker\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Marker\Dao\QuestionMarkerDao;

class QuestionMarkerDaoImpl extends BaseDao implements QuestionMarkerDao
{
    protected $table = 'question_marker';

    public function getQuestionMarker($id)
    {
        $sql            = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $questionMarker = $this->getConnection()->fetchAssoc($sql, array($id));
        return $questionMarker ?: null;
    }

    public function findQuestionMarkersByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks           = str_repeat('?,', count($ids) - 1).'?';
        $sql             = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $questionMarkers = $this->getConnection()->fetchAll($sql, $ids);
        return $questionMarkers;
    }

    public function findQuestionMarkersByMarkerId($markerId)
    {
        $sql             = "SELECT * FROM {$this->table} where markerId = ?";
        $questionMarkers = $this->getConnection()->fetchAll($sql, array($markerId));
        return $questionMarkers;
    }

    public function findQuestionMarkersByQuestionId($questionId)
    {
        $sql             = "SELECT * FROM {$this->table} where questionId = ?";
        $questionMarkers = $this->getConnection()->fetchAll($sql, array($questionId));
        return $questionMarkers;
    }

    public function addQuestionMarker($questionMarker)
    {
        $affected = $this->getConnection()->insert($this->table, $questionMarker);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert questionMarker error.');
        }

        return $this->getQuestionMarker($this->getConnection()->lastInsertId());
    }

    public function updateQuestionMarker($id, $fields)
    {
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
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit)
                        ->orderBy($orderBy[0], $orderBy[1]);
        $questions = $builder->execute()->fetchAll() ?: array();

        return $questions;
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

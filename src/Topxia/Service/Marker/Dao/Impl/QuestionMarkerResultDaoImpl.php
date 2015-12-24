<?php

namespace Topxia\Service\Marker\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Marker\Dao\QuestionMarkerResultDao;

class QuestionMarkerResultDaoImpl extends BaseDao implements QuestionMarkerResultDao
{
    protected $table = 'question_marker_result';

    public function getQuestionMarkerResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addQuestionMarkerResult($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course thread error.');
        }

        return $this->getQuestionMarkerResult($this->getConnection()->lastInsertId());
    }

    public function updateQuestionMarkerResult($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getQuestionMarkerResult($id);
    }

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->getConnection()->delete($this->table, array('questionMarkerId' => $questionMarkerId));
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? and markerId = ?";
        return $this->getConnection()->fetchAll($sql, array($userId, $markerId)) ?: array();
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? and questionMarkerId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $questionMarkerId)) ?: null;
    }
}

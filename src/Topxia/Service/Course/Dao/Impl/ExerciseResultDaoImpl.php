<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseResultDao;

class ExerciseResultDaoImpl extends BaseDao implements ExerciseResultDao
{
	protected $table = 'exercise_result';
    
    public function getExerciseResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addExerciseResult($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert ExerciseResult error.');
        }

        return $this->getExerciseResult($this->getConnection()->lastInsertId());
    }

    public function updateExerciseResult($id,array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getExerciseResult($id);
    }

    public function deleteExerciseResult($id)
    {
		return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getExerciseResultByExerciseIdAndStatusAndUserId($exerciseId, $status, $userId)
    {
        if (empty($exerciseId)  or empty($status) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? AND status = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($exerciseId, $status, $userId)) ? : null;
    }

	public function getExerciseResultByExerciseIdAndUserId($exerciseId, $userId)
	{
		if (empty($exerciseId) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($exerciseId, $userId)) ? : null;
	}
}
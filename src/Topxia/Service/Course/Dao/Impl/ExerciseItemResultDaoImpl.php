<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseItemResultDao;

class ExerciseItemResultDaoImpl extends BaseDao implements ExerciseItemResultDao
{
	protected $table = 'exercise_item_result';

	public function getExerciseItemResult($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getExerciseItemResultByExerciseIdAndStatus($ExerciseId,$status)
	{
		$sql = "SELECT * FROM {$this->table} WHERE ExerciseId = ?  AND status = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ExerciseId,$status)) ? : null;
	}

	public function getExerciseItemResultByExerciseIdAndExerciseResultIdAndQuestionId($ExerciseId,$ExerciseResultId,$questionId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE ExerciseId = ?  AND ExerciseResultId = ? AND questionId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ExerciseId,$ExerciseResultId,$questionId)) ? : null;
	}

	public function addExerciseItemResult($itemResult)
	{
        $affected = $this->getConnection()->insert($this->table, $itemResult);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ExerciseItemResult error.');
        }
        return $this->getExerciseItemResult($this->getConnection()->lastInsertId());
	}

	public function deleteItemResultByExerciseId($exerciseId)
	{
        return $this->getConnection()->delete($this->table,array('exerciseId'=>$exerciseId));
	}

	public function updateExerciseItemResult($ExerciseId,$ExerciseResultId,$questionId,$fields)
	{
		$ExerciseItemResult = $this->getExerciseItemResultByExerciseIdAndExerciseResultIdAndQuestionId($ExerciseId,$ExerciseResultId,$questionId);

        $this->getConnection()->update($this->table, $fields, array('id' => $ExerciseItemResult['id']));
        return true;
	}

	public function findExerciseItemsResultsbyExerciseId($ExerciseId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE ExerciseId = ? ";
        return $this->getConnection()->fetchAll($sql,array($ExerciseId)) ? : array();
	}

	public function findExerciseItemsResultsbyExerciseIdAndUserId($ExerciseId,$userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE ExerciseId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql,array($ExerciseId,$userId)) ? : array();
	}

}
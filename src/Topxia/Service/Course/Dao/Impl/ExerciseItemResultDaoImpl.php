<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseItemResultDao;

class ExerciseItemResultDaoImpl extends BaseDao implements ExerciseItemResultDao
{
	protected $table = 'exercise_item_result';

	public function getItemResult($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getItemResultByExerciseIdAndStatus($exerciseId,$status)
	{
		$sql = "SELECT * FROM {$this->table} WHERE exerciseId = ?  AND status = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($exerciseId,$status)) ? : null;
	}

	public function getItemResultByExerciseIdAndExerciseResultIdAndQuestionId($exerciseId,$exerciseResultId,$questionId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE exerciseId = ?  AND exerciseResultId = ? AND questionId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($exerciseId,$exerciseResultId,$questionId)) ? : null;
	}

	public function addItemResult($itemResult)
	{
        $affected = $this->getConnection()->insert($this->table, $itemResult);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ExerciseItemResult error.');
        }
        return $this->getItemResult($this->getConnection()->lastInsertId());
	}

	public function deleteItemResultByExerciseId($exerciseId)
	{
        return $this->getConnection()->delete($this->table,array('exerciseId'=>$exerciseId));
	}

	public function findExerciseItemsResultsbyExerciseId($exerciseId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? ";
        return $this->getConnection()->fetchAll($sql,array($exerciseId)) ? : array();
	}

	public function findExerciseItemsResultsbyExerciseIdAndUserId($exerciseId,$userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql,array($exerciseId,$userId)) ? : array();
	}

}
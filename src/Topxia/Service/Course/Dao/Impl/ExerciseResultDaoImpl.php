<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseResultDao;

class ExerciseResultDaoImpl extends BaseDao implements ExerciseResultDao
{
	protected $table = 'exercise_result';
    
	public function getExerciseResultByExerciseIdAndUserId($exerciseId, $userId)
	{
		if (empty($exerciseId) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($exerciseId, $userId)) ? : null;
	}
}
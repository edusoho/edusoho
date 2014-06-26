<?php

namespace Topxia\Service\Course\Dao;

interface ExerciseResultDao
{
	public function getExerciseResultByExerciseIdAndUserId($homeworkId, $userId);
}
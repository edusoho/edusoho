<?php 

namespace Topxia\Service\Course\Dao;

interface ExerciseItemResultDao
{
	public function getItemResult($id);

	public function getItemResultByExerciseIdAndStatus($exerciseId,$status);

	public function addItemResult($itemResult);

	public function deleteItemResultByExerciseId($exerciseId);

	public function findItemResultsbyExerciseId($exerciseId);

	public function findItemResultsbyExerciseIdAndUserId($exerciseId,$userId);
	
}
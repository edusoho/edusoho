<?php 

namespace Topxia\Service\Course\Dao;

interface ExerciseItemResultDao
{
	public function getExerciseItemResult($id);

	public function getExerciseItemResultByExerciseIdAndStatus($ExerciseId,$status);

	public function addExerciseItemResult($itemResult);

	public function deleteItemResultByExerciseId($exerciseId);

	public function updateExerciseItemResult($ExerciseId,$ExerciseResultId,$questionId,$fields);

	public function findExerciseItemsResultsbyExerciseId($ExerciseId);

	public function findExerciseItemsResultsbyExerciseIdAndUserId($ExerciseId,$userId);
	
}
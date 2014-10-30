<?php 

namespace Topxia\Service\Course\Dao;

interface ExerciseItemResultDao
{
	public function getExerciseItemResult($id);

	public function getExerciseItemResultByExerciseIdAndStatus($exerciseId,$status);

	public function addExerciseItemResult($itemResult);

	public function deleteItemResultByExerciseId($exerciseId);

	public function updateExerciseItemResult($exerciseId,$exerciseResultId,$questionId,$fields);

	public function findExerciseItemsResultsbyExerciseId($exerciseId);

	public function findExerciseItemsResultsbyExerciseIdAndUserId($exerciseId,$userId);
	
}
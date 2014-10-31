<?php
namespace Topxia\Service\Course;

interface ExerciseService
{
	public function getExercise($id);

	public function getExerciseByLessonId($lessonId);

	public function getItemSetResultByExerciseIdAndUserId($exerciseId,$userId);

	public function createExercise($fields);

	public function startExercise($id,$excludeIds);

    public function submitExercise($id,$exercise);

	public function updateExercise($id, $fields);

	public function deleteExercise($id);

	public function getItemSetByExerciseId($exerciseId);

	public function findExercisesByLessonIds($lessonIds);

	public function canBuildExercise($fields);

}
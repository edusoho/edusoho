<?php
namespace Topxia\Service\Course;

interface ExerciseService
{
	public function getExercise($id);

	public function getExerciseByCourseIdAndLessonId($courseId, $lessonId);

	public function createExercise($fields);

	public function startExercise($id);

	public function updateExercise($id, $fields);

	public function deleteExercise($id);

	public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);

	public function canBuildExercise($fields);

}
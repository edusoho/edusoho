<?php
namespace Topxia\Service\Course;

interface ExerciseService
{
	public function getExercise($id);

	public function createExercise($fields);

	public function updateExercise($id, $fields);

	public function deleteExercise($id);

	public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);
}
<?php

namespace Topxia\Service\Course\Dao;

interface ExerciseDao
{

    public function getExercise($id);

    public function addExercise($fields);

    public function updateExercise($id, $fields);

    public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);

}
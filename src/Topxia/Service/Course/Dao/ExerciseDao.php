<?php

namespace Topxia\Service\Course\Dao;

interface ExerciseDao
{

    public function getExercise($id);

    public function addExercise($fields);

}
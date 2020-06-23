<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseService
{
    public function create($exercise);

    public function get($exerciseId);

    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);

    public function canLearningExercise($exerciseId, $userId);

    public function tryManageExercise($exerciseId);

    public function updateExerciseStatistics($id, $fields);

    public function countStudentsByExerciseId($exerciseId);

    public function hasCourseManagerRole($exerciseId);

    public function isExerciseTeacher($exerciseId, $userId);

    public function changeExerciseCover($id, $coverArray);

    public function updateCategoryByExerciseId($exerciseId, $categoryId);

    public function updateBaseInfo($id, $fields);

    public function getByQuestionBankId($questionBankId);
}

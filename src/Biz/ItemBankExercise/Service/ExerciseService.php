<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseService
{
    const MAX_EXPIRY_DAY = 7300;

    public function create($exercise);

    public function get($exerciseId);

    public function count($conditions);

    public function findByIds($ids);

    public function search($conditions, $orderBy, $start, $limit);

    public function canLearningExercise($exerciseId, $userId);

    public function tryManageExercise($exerciseId, $teacher = 1);

    public function updateExerciseStatistics($id, $fields);

    public function countStudentsByExerciseId($exerciseId);

    public function hasCourseManagerRole($exerciseId);

    public function isExerciseTeacher($exerciseId, $userId);

    public function changeExerciseCover($id, $coverArray);

    public function updateCategoryByExerciseId($exerciseId, $categoryId);

    public function getByQuestionBankId($questionBankId);

    public function updateChapterEnable($exercised, $chapterEnable);

    public function updateBaseInfo($exerciseId, $data);
}

<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseService
{
    const MAX_EXPIRY_DAY = 7300;

    public function update($id, $fields);

    public function create($exercise);

    public function get($exerciseId);

    public function count($conditions);

    public function findByIds($ids);

    public function search($conditions, $orderBy, $start, $limit, $columns = []);

    public function canLearnExercise($exerciseId);

    public function tryManageExercise($exerciseId, $teacher = 1);

    public function updateExerciseStatistics($id, $fields);

    public function countStudentsByExerciseId($exerciseId);

    public function hasExerciseManagerRole($exerciseId);

    public function isExerciseTeacher($exerciseId, $userId);

    public function changeExerciseCover($id, $coverArray);

    public function getByQuestionBankId($questionBankId);

    public function findByQuestionBankIds($questionBankIds);

    public function updateModuleEnable($exercised, $enable);

    public function updateBaseInfo($exerciseId, $data);

    public function deleteExercise($exerciseId);

    public function recommendExercise($exerciseId, $number);

    public function cancelRecommendExercise($exerciseId);

    public function publishExercise($exerciseId);

    public function closeExercise($exerciseId);

    public function unpublishedExercise($exerciseId);

    public function searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit);

    public function searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit);

    public function canTakeItemBankExercise($exerciseId);

    public function canJoinExercise($exerciseId);

    public function freeJoinExercise($exerciseId);

    public function findExercisesByLikeTitle($title);

    public function tryTakeExercise($exerciseId);

    public function publishExerciseChapter($exerciseId, $ids);

    public function unpublishExerciseChapter($exerciseId, $ids);

    public function bindExercise($bindType, $bindId, $exerciseIds);

    public function findBindExercise($bindType, $bindId);

    public function findBindExerciseByBindId($bindId);

    public function findBindExerciseByIds($bindIds);

    public function removeBindExercise($bindExerciseId);

    public function updateBindExercise($bindExercise);

    public function findExerciseAutoJoinRecordByUserIdAndExerciseIds($userId, $exerciseIds);

    public function findExerciseAutoJoinRecordByItemBankExerciseIdAndItemBankExerciseBindIds($itemBankExerciseId, $itemBankExerciseBindIds);

    public function findExerciseAutoJoinRecordByUserIdsAndExerciseId($userIds, $exerciseId);

    public function deleteExerciseAutoJoinRecordByUserIdsAndExerciseBindId($userIds, $exerciseBindId);

    public function deleteExerciseAutoJoinRecordByUserIdAndExerciseBindIds($userId, $exerciseBindIds);

    public function deleteExerciseAutoJoinRecordByExerciseBindId($autoJoinRecordIds);

    public function findExerciseAutoJoinRecordByItemBankExerciseBindIds($itemBankExerciseBindIds);

    public function findExerciseAutoJoinRecordByItemBankExerciseIds($itemBankExerciseIds);

    public function batchCreateExerciseAutoJoinRecord($exerciseAutoJoinRecords);

    public function getExerciseBindById($id);

    public function findExerciseBindByExerciseId($exerciseId);

    public function countExerciseBind($conditions);
}

<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseMemberService
{
    public function count($conditions);

    public function update($id, $member);

    public function search($conditions, $orderBy, $start, $limit, $columns = []);

    public function isExerciseStudent($exerciseId, $userId);

    public function becomeStudent($exerciseId, $userId, $info);

    public function addTeacher($exerciseId);

    public function lockStudent($exerciseId, $userId);

    public function unlockStudent($exerciseId, $userId);

    public function removeStudent($exerciseId, $userId, $reason = []);

    public function getExerciseStudent($exerciseId, $userId);

    public function getExerciseTeacher($exerciseId, $userId);

    public function findMembers($exerciseId, $userId);

    public function remarkStudent($exerciseId, $userId, $remark);

    public function batchUpdateMemberDeadlines($exerciseId, $userIds, $setting);

    public function checkUpdateDeadline($exerciseId, $userIds, $setting);

    public function isMemberNonExpired($exercise, $member);

    public function quitExerciseByDeadlineReach($userId, $exerciseId);

    public function findByUserIdAndRole($userId, $role);

    public function updateMembers($conditions, $updateFields);

    public function updateMasteryRate($exerciseId, $userId);

    public function removeStudents($exerciseId, $userIds, $reason = []);

    public function batchUpdateMembers($updateFields);

    public function batchBecomeStudent($exerciseIds, $userIds, $info, $exercise);

    public function batchRemoveStudent($exerciseId, $userIds);
}

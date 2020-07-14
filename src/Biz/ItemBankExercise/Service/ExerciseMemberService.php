<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseMemberService
{
    public function count($conditions);

    public function update($id, $member);

    public function getByEerciseIdAndUserId($exerciseId, $userId);

    public function search($conditions, $orderBy, $start, $limit, $columns = []);

    public function isExerciseMember($exerciseId, $userId);

    public function becomeStudent($exerciseId, $userId, $info = []);

    public function addTeacher($exerciseId);

    public function lockStudent($exerciseId, $userId);

    public function unlockStudent($exerciseId, $userId);

    public function removeStudent($exerciseId, $userId, $reason = []);

    public function getExerciseMember($exerciseId, $userId);

    public function remarkStudent($exerciseId, $userId, $remark);

    public function batchUpdateMemberDeadlines($exerciseId, $userIds, $setting);

    public function checkUpdateDeadline($exerciseId, $userIds, $setting);

    public function isMemberNonExpired($exercise, $member);

    public function quitExerciseByDeadlineReach($userId, $exerciseId);
}

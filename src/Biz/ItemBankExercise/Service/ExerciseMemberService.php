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

    public function getExerciseMember($exerciseId, $userId);

    public function remarkStudent($exerciseId, $userId, $remark);

    public function batchUpdateMemberDeadlinesByDay($exerciseId, $userIds, $day, $waveType = 'plus');

    public function checkDayAndWaveTypeForUpdateDeadline($exerciseId, $userIds, $day, $waveType = 'plus');

    public function batchUpdateMemberDeadlinesByDate($exerciseId, $userIds, $date);

    public function checkDeadlineForUpdateDeadline($exerciseId, $userIds, $date);
}

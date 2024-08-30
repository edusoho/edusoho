<?php

namespace Biz\Classroom\Service;

interface MemberService
{
    public function findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange($classroomId, $role, $startTime, $endTime, $format = '%Y-%m-%d');

    public function getClassroomMember($classroomId, $userId);
}

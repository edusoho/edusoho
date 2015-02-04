<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomMemberDao
{
    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function getMemberByClassroomIdAndUserId($classroomId, $userId);

    public function updateMember($id, $member);
}

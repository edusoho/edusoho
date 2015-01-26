<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomMemberDao
{
    public function getMemberByClassIdAndUserId($classId, $userId);
}
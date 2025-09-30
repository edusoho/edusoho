<?php

namespace Biz\Classroom\Service\Impl;

use Biz\BaseService;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Classroom\Service\MemberService;

class MemberServiceImpl extends BaseService implements MemberService
{
    const ROLE_STUDENT = 'student';

    const ROLE_AUDITOR = 'auditor';

    const ROLE_TEACHER = 'teacher';

    const ROLE_ASSISTANT = 'assistant';

    const ROLE_HEAD_TEACHER = 'headTeacher';

    public function findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange($classroomId, $role, $startTime, $endTime, $format = '%Y-%m-%d')
    {
        return $this->getMemberDao()->findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange($classroomId, $role, $startTime, $endTime, $format);
    }

    public function getClassroomMember($classroomId, $userId)
    {
        return $this->getMemberDao()->getByClassroomIdAndUserId($classroomId, $userId);
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Classroom:ClassroomMemberDao');
    }
}

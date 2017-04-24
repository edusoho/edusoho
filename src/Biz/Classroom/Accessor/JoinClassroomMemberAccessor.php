<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\Service\ClassroomService;

class JoinClassroomMemberAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked');
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member) || $member['role'] == array('auditor')) {
            return true;
        }

        return $this->buildResult('classroom.member_exist');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}

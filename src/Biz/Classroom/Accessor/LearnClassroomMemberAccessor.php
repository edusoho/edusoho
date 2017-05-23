<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\Service\ClassroomService;

class LearnClassroomMemberAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked', array('userId' => $user['id']));
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member)) {
            return $this->buildResult('member.not_found');
        }
        if ($member['role'] == array('auditor')) {
            return $this->buildResult('member.auditor');
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('member.expired', array('userId' => $user['id']));
        }

        return null;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}

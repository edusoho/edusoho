<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\User\UserException;

class LearnClassroomMemberAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('UN_LOGIN', array(), UserException::EXCEPTION_MODUAL);
        }

        if ($user['locked']) {
            return $this->buildResult('LOCKED_USER', array('userId' => $user['id']), UserException::EXCEPTION_MODUAL);
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member)) {
            return $this->buildResult('NOTFOUND_MEMBER', array(), ClassroomException::EXCEPTION_MODUAL);
        }
        if ($member['role'] == array('auditor')) {
            return $this->buildResult('FORBIDDEN_AUDITOR_LEARN', array(), ClassroomException::EXCEPTION_MODUAL);
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('EXPIRED_MEMBER', array('userId' => $user['id']), ClassroomException::EXCEPTION_MODUAL);
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

<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\User\UserException;

class JoinClassroomMemberAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult(UserException::EXCEPTION_MODUAL, 'UN_LOGIN');
        }

        if ($user['locked']) {
            return $this->buildResult(UserException::EXCEPTION_MODUAL, 'LOCKED_USER', array('userId' => $user['id']));
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member) || $member['role'] == array('auditor')) {
            return null;
        }

        return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'DUPLICATE_JOIN', array('userId' => $user['id']));
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}

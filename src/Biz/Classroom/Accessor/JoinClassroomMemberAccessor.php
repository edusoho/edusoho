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
            return $this->buildResult('UN_LOGIN', array(), UserException::EXCEPTION_MODUAL);
        }

        if ($user['locked']) {
            return $this->buildResult('LOCKED_USER', array('userId' => $user['id']), UserException::EXCEPTION_MODUAL);
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
        if (empty($member) || $member['role'] == array('auditor')) {
            return null;
        }

        return $this->buildResult('DUPLICATE_JOIN', array('userId' => $user['id']), ClassroomException::EXCEPTION_MODUAL);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}

<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\MemberException;
use Biz\Course\Service\MemberService;
use Biz\User\UserException;

class JoinCourseMemberAccessor extends AccessorAdapter
{
    public function access($course)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('UN_LOGIN', array(), UserException::EXCEPTION_MODUAL);
        }

        if ($user['locked']) {
            return $this->buildResult('LOCKED_USER', array('userId' => $user['id']), UserException::EXCEPTION_MODUAL);
        }

        if ($this->getCourseMemberService()->getCourseMember($course['id'], $user->getId())) {
            return $this->buildResult('DUPLICATE_MEMBER', array('userId' => $user['id']), MemberException::EXCEPTION_MODUAL);
        }

        return null;
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}

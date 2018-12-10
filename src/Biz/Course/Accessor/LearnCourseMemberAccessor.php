<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\MemberException;
use Biz\Course\Service\MemberService;
use Biz\User\UserException;

class LearnCourseMemberAccessor extends AccessorAdapter
{
    public function access($course)
    {
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            return null;
        }

        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('UN_LOGIN', array(), UserException::EXCEPTION_MODUAL);
        }

        if ($user['locked']) {
            return $this->buildResult('LOCKED_USER', array('userId' => $user['id']), UserException::EXCEPTION_MODUAL);
        }

        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        if (empty($member)) {
            return $this->buildResult('NOTFOUND_MEMBER', array(), MemberException::EXCEPTION_MODUAL);
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('EXPIRED_MEMBER', array('userId' => $user['id']), MemberException::EXCEPTION_MODUAL);
        }

        return null;
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}

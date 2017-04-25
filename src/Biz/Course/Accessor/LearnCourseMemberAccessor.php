<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\MemberService;

class LearnCourseMemberAccessor extends AccessorAdapter implements AccessorInterface
{
    public function access($course)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked');
        }

        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        if (empty($member)) {
            return $this->buildResult('member.not_exist');
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('member.expired');
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

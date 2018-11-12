<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\CourseException;
use Biz\Course\Service\MemberService;
use Biz\User\UserException;

class JoinCourseMemberAccessor extends AccessorAdapter
{
    public function access($course)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult(UserException::EXCEPTION_MODUAL, 'UN_LOGIN');
        }

        if ($user['locked']) {
            return $this->buildResult(UserException::EXCEPTION_MODUAL, 'LOCKED_USER', array('userId' => $user['id']));
        }

        if ($this->getCourseMemberService()->getCourseMember($course['id'], $user->getId())) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'DUPLICATE_MEMBER', array('userId' => $user['id']));
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

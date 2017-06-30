<?php

namespace Biz\Event\Service\Impl;

use Biz\BaseService;
use Biz\Event\Service\EventSubject;

class CourseMemberSubject extends BaseService implements EventSubject
{
    public function getSubject($subjectId)
    {
        if (empty($subjectId)) {
            return null;
        }
        $user = $this->getCurrentUser();

        return $this->getCourseMemberService()->getCourseMember($subjectId, $user->getId());
    }

    private function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}

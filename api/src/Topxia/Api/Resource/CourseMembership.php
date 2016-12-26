<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CourseMembership extends BaseResource
{

    public function get(Application $app, Request $request, $courseId, $userId)
    {
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);

        if (empty($member)) {
            return array('membership' => 'none');
        }

        return array('membership' => $member['role']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    public function filter($res)
    {
        return $res;
    }
}

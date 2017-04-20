<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Api\Resource\BaseResource;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class Members extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $conditions = array('courseId' => $courseId);
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        //需要区分教师吗？8.0以前版本没有
        $total = $this->getCourseMemberService()->countMembers($conditions);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
        $members = $this->assemblyMembers($members);
        return $this->wrap($this->filter($members), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course/Member', $res);
    }

    protected function assemblyMembers($members)
    {
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($members, 'courseId'));

        foreach ($members as &$member) {
            if (empty($member['updatedTime'])) {
                $member['updatedTime'] = $member['createdTime'];
            }
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
            $member['course'] = empty($courses[$member['courseId']]) ? null : $courses[$member['courseId']];
        }

        return $members;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}

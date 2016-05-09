<?php

namespace Topxia\Api\Resource\Course;

use Topxia\Api\Resource\BaseResource;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Members extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $conditions = array('courseId' => $courseId);
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $total = $this->getCourseService()->searchMemberCount($conditions);
        $members = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $limit);
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
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}

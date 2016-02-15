<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseMembers extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $conditions = array('courseId' => $courseId);
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $total = $this->getCourseService()->searchMemberCount($conditions);
        $members = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $limit);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
        }

        return $this->wrap($this->filter($members), $total);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('CourseMember', $res);
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
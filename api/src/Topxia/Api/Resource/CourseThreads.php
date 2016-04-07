<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseThreads extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'posted');
        $simplify = $request->query->get('simplify', 0);

        $conditions = array(
            'courseId' => $courseId
        );

        $total = $this->getCourseThreadService()->searchThreadCount($conditions);

        $courseThreads = $this->getCourseThreadService()->searchThreads($conditions, $sort, $start, $limit);

        $userIds = ArrayToolkit::column($courseThreads, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($courseThreads as $key => $value) {
            $courseThreads[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        $courseThreads = $this->filter($courseThreads);

        if ($simplify) {
            $courseThreads = $this->simplify($courseThreads);
        }

        return $this->wrap($courseThreads, $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('CourseThread', $res);
    }

    protected function simplify($res)
    {
        return $this->multicallSimplify('CourseThread', $res);
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}

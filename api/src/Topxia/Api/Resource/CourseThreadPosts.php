<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseThreadPosts extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $threadId)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'createdTime');

        $conditions = array(
            'courseId' => $courseId,
            'threadId' => $threadId,
        );

        $total = $this->getCourseThreadService()->searchThreadPostsCount($conditions);
        $courseThreadPosts = $this->getCourseThreadService()->searchThreadPosts($conditions, $sort, $start, $limit);

        $userIds = ArrayToolkit::column($courseThreadPosts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($courseThreadPosts as $key => $value) {
            $courseThreadPosts[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        $courseThreadPosts = $this->filter($courseThreadPosts);

        return $this->wrap($courseThreadPosts, $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('CourseThreadPost', $res);
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

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseThreadPosts extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $threadId)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'createdTime');

        if (!$this->getCourseService()->canTakeCourse($courseId)) {
            return $this->error('403', '无权限查看');
        }

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
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseThreadPost extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $threadId, $postId)
    {
        if (!$this->getCourseService()->canTakeCourse($courseId)) {
            return $this->error('403', '无权限查看');
        }

        $courseThreadPost = $this->getCourseThreadService()->getPost($courseId, $postId);

        $user = $this->getUserService()->getUser($courseThreadPost['userId']);
        $courseThreadPost['user'] = $this->simpleUser($user);

        return $this->filter($courseThreadPost);
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
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

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseThread extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $threadId)
    {
        if (!$this->getCourseService()->canTakeCourse($courseId)) {
            return $this->error('403', '无权限查看');
        }

        $courseThread = $this->getCourseThreadService()->getThread($courseId, $threadId);

        $user = $this->getUserService()->getUser($courseThread['userId']);
        $courseThread['user'] = $this->simpleUser($user);

        $course = $this->getCourseService()->getCourse($courseThread['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $course['courseSet'] = $courseSet;
        $courseThread['course'] = $this->callFilter('Course', $course);
        return $this->filter($courseThread);
    }

    public function filter($res)
    {
        $res['latestPostTime'] = date('c', $res['latestPostTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $res['content'] = convertAbsoluteUrl($res['content']);
        return $res;
    }

    protected function simplify($res)
    {
        $simple = array();

        $simple['id'] = $res['id'];
        $simple['title'] = $res['title'];
        $simple['content'] = mb_substr(strip_tags($res['content']), 0, 100, 'utf-8');
        $simple['postNum'] = $res['postNum'];
        $simple['hitNum'] = $res['hitNum'];
        $simple['userId'] = $res['userId'];
        $simple['courseId'] = $res['courseId'];
        $simple['type'] = $res['type'];

        if (isset($res['user'])) {
            $simple['user'] = $res['user'];
        }

        return $simple;
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

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}

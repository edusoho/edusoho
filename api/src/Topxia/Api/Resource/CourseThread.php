<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CourseThread extends BaseResource
{
    public function get(Application $app, Request $request, $courseId, $threadId)
    {
        $courseThread = $this->getCourseThreadService()->getThread($courseId, $threadId);

        $user                 = $this->getUserService()->getUser($courseThread['userId']);
        $courseThread['user'] = $this->simpleUser($user);

        $course                          = $this->getCourseService()->getCourse($courseThread['courseId']);
        $courseThread['course']['id']    = $course['id'];
        $courseThread['course']['title'] = $course['title'];

        return $this->filter($courseThread);
    }

    public function filter($res)
    {
        $res['latestPostTime'] = date('c', $res['latestPostTime']);
        $res['createdTime']    = date('c', $res['createdTime']);
        $res['updatedTime']    = date('c', $res['updatedTime']);
        return $res;
    }

    protected function simplify($res)
    {
        $simple = array();

        $simple['id']       = $res['id'];
        $simple['title']    = $res['title'];
        $simple['content']  = mb_substr(strip_tags($res['content']), 0, 100, 'utf-8');
        $simple['postNum']  = $res['postNum'];
        $simple['hitNum']   = $res['hitNum'];
        $simple['userId']   = $res['userId'];
        $simple['courseId'] = $res['courseId'];
        $simple['type']     = $res['type'];

        if (isset($res['user'])) {
            $simple['user'] = $res['user'];
        }

        return $simple;
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
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

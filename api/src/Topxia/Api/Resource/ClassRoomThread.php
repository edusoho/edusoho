<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomThread extends BaseResource
{
 
    public function get(Application $app, Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);

        $user = $this->getUserService()->getUser($thread['userId']);
        $thread['user'] = $this->simpleUser($user);
        $classroom = $this->getClassroomService()->getClassRoom($thread['targetId']);
        $thread['target']['id'] = $classroom['id'];
        $thread['target']['title'] = $classroom['title'];

        return $this->filter($thread);
    }

    public function filter($res)
    {
        $res['lastPostTime'] = date('c', $res['lastPostTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        return $res;
    }

    protected function simplify($res)
    {
        $simple = array();

        $simple['id'] = $res['id'];
        $simple['title'] = $res['title'];
        $simple['content'] = substr(strip_tags($res['content']), 0, 100);
        $simple['postNum'] = $res['postNum'];
        $simple['hitNum'] = $res['hitNum'];
        $simple['userId'] = $res['userId'];
        $simple['classRoomId'] = $res['classRoomId'];
        $simple['type'] = $res['type'];

        if (isset($res['user'])) {
            $simple['user'] = $res['user'];
        }

        return $simple;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}

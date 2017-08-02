<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomThread extends BaseResource
{
    public function get(Application $app, Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);

        if (empty($thread)) {
            return $this->error('not_found', '没有找到');
        }

        if (!$this->getClassroomService()->canTakeClassroom($thread['targetId'])) {
            return $this->error('403', '无权限查看');
        }

        $user = $this->getUserService()->getUser($thread['userId']);
        $thread['user'] = $this->simpleUser($user);
        $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);
        $thread['target']['id'] = $classroom['id'];
        $thread['target']['title'] = $classroom['title'];

        return $this->filter($thread);
    }

    public function filter($res)
    {
        $res['content'] = convertAbsoluteUrl($res['content']);
        $res['lastPostTime'] = date('c', $res['lastPostTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', isset($res['updateTime']) ? $res['updateTime'] : $res['updatedTime']);
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
        return $this->getServiceKernel()->createService('Thread:ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}

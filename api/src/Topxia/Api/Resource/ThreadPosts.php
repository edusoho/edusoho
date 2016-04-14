<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class ThreadPosts extends BaseResource
{
	public function get(Application $app, Request $request, $threadId)
    {   
        $type = $request->query->get('type', 'course');
        $posts = array();
        if ("course" == $type) {
            $posts = $this->getPostByCourse($threadId);
        } else {
            $posts = $this->getPostByClassRoom($threadId);
        }

        $userIds = ArrayToolkit::column($posts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($posts as $key => $value) {
            $posts[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        return $this->wrap($this->filter($posts));
    }

    protected function getPostByClassRoom($threadId) {
        $conditions = array(
            'threadId' => $threadId,
            'parentId' => 0
        );
        $count = $this->getThreadService()->searchPostsCount($conditions);

        return $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime', 'asc'),
            0,
            $count
        );
    }

    protected function getPostByCourse($threadId) {
        $thread = $this->getCourseThreadService()->getThread($threadId);
        $total = $this->getCourseThreadService()->getThreadPostCount($thread['courseId'], $threadId);
        return $this->getCourseThreadService()->findThreadPosts($thread['courseId'], $threadId, 'elite', 0, $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('ThreadPost', $res);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
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
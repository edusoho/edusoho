<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class ThreadPosts extends BaseResource
{
	public function get(Application $app, Request $request, $threadId)
    {
        $conditions = array(
            'threadId' => $threadId,
            'parentId' => 0
        );
        $count = $this->getThreadService()->searchPostsCount($conditions);

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime', 'asc'),
            0,
            $count
        );

        $userIds = ArrayToolkit::column($posts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($posts as $key => $value) {
            $posts[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        return $this->wrap($this->filter($posts));
    }

    public function filter($res)
    {
        return $this->multicallFilter('ThreadPost', $res);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
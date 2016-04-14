<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class ThreadPosts extends BaseResource
{
	public function get(Application $app, Request $request, $threadId)
    {
        $start       = $request->query->get('start', 0);
        $limit       = $request->query->get('limit', 10);

        $conditions = array(
            'threadId' => $threadId,
            'parentId' => 0
        );
        $count = $this->getThreadService()->searchPostsCount($conditions);

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime', 'asc'),
            0,
            100
        );

        return $posts;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}
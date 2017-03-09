<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ThreadPosts extends BaseResource
{
    public function get(Application $app, Request $request, $threadId)
    {
        $type = $request->query->get('type', 'course');
        $courseId = $request->query->get('courseId', 0);
        $posts = array();
        if ($type == 'course') {
            if ($courseId == 0) {
                $thread = $this->getCourseThreadService()->getThread($courseId, $threadId);
                $courseId = $thread['courseId'];
            }

            $conditions = array(
                'threadId' => $threadId,
            );
            $total = $this->getCourseThreadService()->getThreadPostCount($courseId, $threadId);
            $posts = $this->getCourseThreadService()->searchThreadPosts(
                $conditions,
                'elite',
                0,
                $total
            );
        } else {
            $conditions = array(
                'threadId' => $threadId,
                'parentId' => 0,
            );
            $total = $this->getThreadService()->searchPostsCount($conditions);
            $posts = $this->getThreadService()->searchPosts(
                $conditions,
                array('createdTime' => 'ASC'),
                0,
                $total
            );
        }

        $userIds = ArrayToolkit::column($posts, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($posts as $key => $value) {
            $posts[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        return $this->wrap($this->filter($posts), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('ThreadPost', $res);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread:ThreadService');
    }

    protected function getCourseThreadService()
    {
        return $this->getServiceKernel()->createService('Course:ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}

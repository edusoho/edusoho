<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomThreads extends BaseResource
{
    public function get(Application $app, Request $request, $classRoomId)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'posted');
        $simplify = $request->query->get('simplify', 0);

        $conditions = array(
            'targetType' => 'classroom',
            'targetId' => $classRoomId,
        );

        $total = $this->getThreadService()->searchThreadCount($conditions);

        $threads = $this->getThreadService()->searchThreads($conditions, $sort, $start, $limit);

        $userIds = ArrayToolkit::column($threads, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($threads as $key => $value) {
            $threads[$key]['user'] = $this->simpleUser($users[$value['userId']]);
        }

        $threads = $this->filter($threads);

        if ($simplify) {
            $threads = $this->simplify($threads);
        }

        return $this->wrap($threads, $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('ClassRoomThread', $res);
    }

    protected function simplify($res)
    {
        return $this->multicallSimplify('ClassRoomThread', $res);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread:ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}

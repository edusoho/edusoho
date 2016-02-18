<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Status extends BaseResource
{
    public function get(Application $app, Request $request, $userId, $courseId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            return $this->error(404, "用户(#{$userId})不存在");
        }

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $statuses = $this->getStatusService()->searchStatuses(array('userId' => $userId, 'courseId' => $courseId), array('createdTime', 'DESC'), $start, $limit);

        return $this->filter($statuses);
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}

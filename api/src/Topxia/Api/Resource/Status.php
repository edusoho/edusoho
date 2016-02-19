<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
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

        return $this->_filterStatus($statuses);
    }

    public function filter(&$res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'userId', 'courseId', 'classroomId', 'type', 'objectType', 'objectId', 'properties', 'createdTime'));

        return $res;
    }

    private function _filterStatus(&$res)
    {
        foreach ($res as $key => &$item) {
            unset($item['private']);
            unset($item['commentNum']);
            unset($item['likeNum']);
        }

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

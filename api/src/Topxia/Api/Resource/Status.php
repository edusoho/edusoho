<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Status extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $statuses = $this->getStatusService()->searchStatuses(array('courseId' => $courseId), array('createdTime', 'DESC'), $start, $limit);

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
}

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\CloudAPIFactory;

class LessonLiveTicket extends BaseResource
{
    public function get(Application $app, Request $request, $id, $ticket)
    {
        $task = $this->getTaskService()->getTask($id);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (!empty($activity['syncId'])) {
            $ticket = $this->getS2B2CFacadeService()->getS2B2CService()->consumeLiveEntryTicket($activity['ext']['liveId'], $ticket);
        } else {
            $ticket = CloudAPIFactory::create('leaf')->get("/liverooms/{$activity['ext']['liveId']}/tickets/{$ticket}");
        }
        return $ticket;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }
}

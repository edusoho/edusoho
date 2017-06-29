<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\CloudAPIFactory;

class LessonLiveTicket extends BaseResource
{
    public function get(Application $app, Request $request, $id, $ticket)
    {
        $ticket = CloudAPIFactory::create('leaf')->get("/liverooms/{$id}/tickets/{$ticket}");
        return $ticket;
    }

    public function filter($res)
    {
        return $res;
    }
}

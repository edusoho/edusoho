<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ClassroomMember extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId, $memberId)
    {

    }

    public function filter(&$res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

}
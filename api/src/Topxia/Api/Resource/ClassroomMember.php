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
        unset($res['userId']);
        $res['user'] = $this->callSimplify('User', $res['user']);
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

}
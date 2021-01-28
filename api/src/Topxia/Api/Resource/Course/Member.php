<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class Member extends BaseResource
{

    public function filter($res)
    {
        unset($res['userId']);
        $res['user'] = $this->callSimplify('User', $res['user']);
        $res['course'] = $this->callSimplify('Course', $res['course']);
        $res['noteLastUpdateTime'] = date('c', $res['noteLastUpdateTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }
}

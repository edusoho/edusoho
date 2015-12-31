<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CourseMember extends BaseResource
{

    public function filter(&$res)
    {
        unset($res['userId']);
        $res['user'] = $this->callSimplify('User', $res['user']);
        $res['noteLastUpdateTime'] = date('c', $res['noteLastUpdateTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

}
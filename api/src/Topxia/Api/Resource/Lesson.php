<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Lesson extends BaseResource
{
    public function filter(&$res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        return $res;
    }
}

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{

    public function filter(&$res)
    {
        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id'] = $res['id'];
        $simple['nickname'] = $res['nickname'];
        $simple['title'] = $res['title'];
        $simple['avatar'] = $this->getFileUrl($res['smallAvatar']);

        return $simple;
    }

}
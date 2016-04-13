<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomThread extends BaseResource
{
   
    public function filter($res)
    {
        $res['lastPostTime'] = date('c', $res['lastPostTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        return $res;
    }

    protected function simplify($res)
    {
        $simple = array();

        $simple['id'] = $res['id'];
        $simple['title'] = $res['title'];
        $simple['content'] = substr(strip_tags($res['content']), 0, 100);
        $simple['postNum'] = $res['postNum'];
        $simple['hitNum'] = $res['hitNum'];
        $simple['userId'] = $res['userId'];
        $simple['classRoomId'] = $res['classRoomId'];
        $simple['type'] = $res['type'];

        if (isset($res['user'])) {
            $simple['user'] = $res['user'];
        }

        return $simple;
    }

}

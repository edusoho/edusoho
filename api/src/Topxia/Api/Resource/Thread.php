<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Thread extends BaseResource
{

    public function filter(&$res)
    {
  
        $res['updateTime'] = date('c', $res['updateTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['threadId'] = $res['id'];

        unset($res['id']);
        unset($res['relationId']);
        unset($res['categoryId']);
        unset($res['ats']);
        unset($res['nice']);
        unset($res['sticky']);
        unset($res['solved']);
        unset($res['lastPostUserId']);
        unset($res['lastPostTime']);
        unset($res['location']);
        unset($res['memberNum']);
        unset($res['maxUsers']);
        unset($res['actvityPicture']);
        unset($res['status']);
        unset($res['startTime']);
        unset($res['endTime']);
        unset($res['body']);

        return $res;
    }

}

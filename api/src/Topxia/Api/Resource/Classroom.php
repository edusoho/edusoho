<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Classroom extends BaseResource
{
    public function filter(&$res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);

        foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        return $res;
    }

    public function get(Application $app, Request $request, $id)
    {
    }

    public function post(Application $app, Request $request, $id)
    {
    }
}

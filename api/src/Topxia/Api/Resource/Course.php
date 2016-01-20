<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Course extends BaseResource
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



}
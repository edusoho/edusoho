<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;

/**
 * 兼容模式，对应course_task.
 */
class Lesson extends BaseResource
{
    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }
}

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class ClassroomStatus extends BaseResource
{
    public function filter($res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'userId', 'courseId', 'classroomId', 'type', 'objectType', 'objectId', 'properties', 'createdTime'));

        if (!empty($res['properties']['course']['picture'])) {
            $res['properties']['course']['picture'] = $this->getFileUrl($res['properties']['course']['picture']);
        }
        
        return $res;
    }
}

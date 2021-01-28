<?php

namespace Topxia\Api\Resource\Classroom;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Resource\BaseResource;

class Member extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId, $memberId)
    {
        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $memberId);
        if (empty($classroomMember)) {
            return array();
        }
        return $this->filter($classroomMember);
    }

    public function filter($res)
    {
        // unset($res['userId']);
        if (!empty($res['user'])) {
            $res['user'] = $this->callSimplify('User', $res['user']);
        }

        if (!empty($res['classroom'])) {
            $res['classroom'] = $this->callSimplify('Classroom', $res['classroom']);
        }

        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}

<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ClassroomMember extends BaseResource
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
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}

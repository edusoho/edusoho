<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ClassroomMembers extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId)
    {
        $conditions = array('classroomId' => $classroomId);
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $limit);
        $total = $this->getClassroomService()->searchMemberCount($conditions);

        return $this->wrap($this->filter($members), $total);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('ClassroomMember', $res);
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
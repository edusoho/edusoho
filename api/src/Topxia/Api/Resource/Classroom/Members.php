<?php

namespace Topxia\Api\Resource\Classroom;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Resource\BaseResource;
use Topxia\Service\Common\ServiceKernel;

class Members extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId)
    {
        $conditions = array('classroomId' => $classroomId);
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $role = $request->query->get('role', '');

        if (!empty($role)) {
            $conditions['role'] = $role;
        }

        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime' => 'DESC'), $start, $limit);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
        }

        return $this->wrap($this->filter($members), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Classroom/Member', $res);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}

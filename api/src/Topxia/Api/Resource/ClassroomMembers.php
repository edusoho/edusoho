<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class ClassroomMembers extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = array();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        if ($request->query->has('cursor')) {
            $cursor = $request->query->get('cursor', 0);
            $conditions['createdTime_GE'] = $cursor;
            $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime' => 'ASC'), $start, $limit);
            $members = $this->assemblyMembers($members);
            $next = $this->nextCursorPaging($cursor, $start, $limit, $members);

            return $this->wrap($this->filter($members), $next);
        } else {
            //@todo 暂不支持
            return $this->wrap(array(), 0);
        }
    }

    public function filter($res)
    {
        return $this->multicallFilter('Classroom/Member', $res);
    }

    protected function assemblyMembers($members)
    {
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        $courses = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($members, 'classroomId'));

        foreach ($members as &$member) {
            if (empty($member['updatedTime'])) {
                $member['updatedTime'] = $member['createdTime'];
            }
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
            $member['classroom'] = empty($courses[$member['classroomId']]) ? null : $courses[$member['classroomId']];
        }

        return $members;
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

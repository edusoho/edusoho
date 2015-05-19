<?php

namespace Classroom\Service\Classroom\Impl;

use Topxia\Service\Common\BaseService;
use Classroom\Service\Classroom\ClassroomThreadService;
use Topxia\Common\ArrayToolkit;

class ClassroomThreadServiceImpl extends BaseService implements ClassroomThreadService
{
    public function setUserBadgeTitle($classroomId, $users)
    {
        $userIds = ArrayToolkit::column($users, 'id');
        $classroomMembers = $this->getClassroomService()->findMembersByClassroomIdAndUserIds($classroomId, $userIds);
        foreach ($classroomMembers as $member) {
            if (in_array($member['userId'], $userIds) && $member['role'] != 'student' && $member['role'] != 'auditor') {
                $user = $users[$member['userId']];
                $user['badgeTitle'] = $member['role'];
                $users[$member['userId']] = $user;
            }
        }

        return $users;
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }
}

<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class UtilityController extends BaseController
{
    public function headteacherMatchAction(Request $request, $classroomId)
    {
        $likeString = $request->query->get('q');
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $users = $this->getUserService()->searchUsers(array(
            'nickname' => $likeString,
            'roles' => 'ROLE_TEACHER',
            'excludeIds' => array($classroom['headTeacherId']),
        ), array('createdTime' => 'DESC'), 0, 10
        );

        $newUsers = array();
        foreach ($users as $user) {
            $newUsers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
            );
        }

        return $this->createJsonResponse($newUsers);
    }

    public function assistantsMatchAction(Request $request, $classroomId)
    {
        $likeString = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(array('nickname' => $likeString),
            array(), 0, 10);
        $userIds = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');

        $excludeUserIds = $this->_getExcludeIds($classroomId);
        $conditions = array(
            'classroomId' => $classroomId,
            'role' => 'student',
            'userIds' => $userIds,
            'excludeUserIds' => $excludeUserIds,
        );
        $students = $this->getClassroomService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $newUsers = array();
        foreach ($users as $user) {
            $newUsers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
            );
        }

        return $this->createJsonResponse($newUsers);
    }

    private function _getExcludeIds($classroomId)
    {
        return $this->getClassroomService()->findAssistants($classroomId);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}

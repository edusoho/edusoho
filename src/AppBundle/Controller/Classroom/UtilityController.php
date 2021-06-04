<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class UtilityController extends BaseController
{
    public function headteacherMatchAction(Request $request, $classroomId)
    {
        $likeString = $request->query->get('q');
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $users = $this->getUserService()->searchUsers([
            'nickname' => $likeString,
            'roles' => '|ROLE_TEACHER|',
            'excludeIds' => [$classroom['headTeacherId']],
        ], ['createdTime' => 'DESC'], 0, 10
        );

        $newUsers = [];
        foreach ($users as $user) {
            $newUsers[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
            ];
        }

        return $this->createJsonResponse($newUsers);
    }

    public function assistantsMatchAction(Request $request, $classroomId)
    {
        $likeString = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(['nickname' => $likeString],
            [], 0, 10);
        $userIds = empty($users) ? [-1] : ArrayToolkit::column($users, 'id');

        $excludeUserIds = $this->_getExcludeIds($classroomId);
        $conditions = [
            'classroomId' => $classroomId,
            'role' => 'student',
            'userIds' => $userIds,
            'excludeUserIds' => $excludeUserIds,
        ];
        $students = $this->getClassroomService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $userIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $newUsers = [];
        foreach ($users as $user) {
            $newUsers[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
            ];
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

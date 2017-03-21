<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

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
        $users = $this->getUserService()->searchUsers(array(
            'nickname' => $likeString,
            'excludeIds' => $this->_getExcludeIds($classroomId),
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

    private function _getExcludeIds($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $assistantIds = $this->getClassroomService()->findAssistants($classroom['id']);
        $excludeIds = $assistantIds;

        return $excludeIds;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}

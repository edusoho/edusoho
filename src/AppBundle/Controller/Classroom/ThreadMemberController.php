<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class ThreadMemberController extends BaseController
{
    public function becomeAction(Request $request, $classroomId, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);

        if (empty($member)) {
            $this->createNewException(ClassroomException::NOTFOUND_MEMBER());
        }

        return $this->forward('AppBundle:Thread/Member:become', array(
            'request' => $request,
            'threadId' => $threadId,
        ));
    }

    public function quitAction(Request $request, $threadId, $memberId)
    {
        return $this->forward('AppBundle:Thread/Member:quit', array(
            'request' => $request,
            'threadId' => $threadId,
            'memberId' => $memberId,
        ));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}

<?php
namespace Classroom\ClassroomBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class MemberController extends BaseController
{
    public function becomeAction(Request $request, $classroomId, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录!不能加入活动!');
        }

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);

        if (empty($member)) {
            throw $this->createAccessDeniedException('不是本班成员!不能加入活动!');
        }

        return $this->forward('TopxiaWebBundle:Thread/Member:become', array(
            'request'  => $request,
            'threadId' => $threadId
        ));
    }

    public function quitAction(Request $request, $threadId, $memberId)
    {
        return $this->forward('TopxiaWebBundle:Thread/Member:quit', array(
            'request'  => $request,
            'threadId' => $threadId,
            'memberId' => $memberId
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}

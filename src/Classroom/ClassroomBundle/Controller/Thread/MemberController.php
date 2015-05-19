<?php
namespace Classroom\ClassroomBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class MemberController extends BaseController
{
    public function becomeAction(Request $request, $classroomId, $threadId)
    {
        return $this->forward('TopxiaWebBundle:Thread/Member:become', array('
            request' => $request,
            'threadId' => $threadId,
        ));
    }

    public function quitAction(Request $request, $threadId, $memberId)
    {
        return $this->forward('TopxiaWebBundle:Thread/Member:quit', array(
            'request' => $request,
            'threadId' => $threadId,
            'memberId' => $memberId,
        ));
    }
}

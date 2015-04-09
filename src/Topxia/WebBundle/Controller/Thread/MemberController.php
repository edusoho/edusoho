<?php
namespace Topxia\WebBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MemberController extends BaseController
{
    public function becomeAction(Request $request, $threadId)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getCurrentUser();
            if (!$user->isLogin()) {
                $this->createAccessDeniedException('未登录,不能操作!');
            }
            $data = $request->request->all();
            $member = array(
                'threadId' => $threadId,
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'truename' => $data['truename'],
                'mobile' => $data['mobile']
            );

            $member = $this->getThreadService()->createMember($member);
            return $this->createJsonResponse(empty($member) ? false : true);
        }

        $thread = $this->getThreadService()->getThread($threadId);
        return $this->render('TopxiaWebBundle:Thread/Widget:user-info-modal.html.twig', array(
            'thread' => $thread,
        ));
    }

    public function quitAction(Request $request, $memberId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('未登录,不能操作!');
        }

        $this->getThreadService()->deleteMember($memberId);

        return $this->createJsonResponse(true);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}

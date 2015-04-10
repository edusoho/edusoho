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

    public function showMembersAction(Request $request, $thread)
    {
        $members = $this->_findPageMembers($request, $thread['id']);
        $myFriends = $this->_findMyJoindedFriends($members);
        $membersCount = $this->getThreadService()->findMembersCountByThreadId($thread['id']);

        return $this->render('TopxiaWebBundle:Thread/Event:user-grids.html.twig', array(
            'members' => $members,
            'myFriends' => $myFriends,
            'threadId' => $thread['id'],
            'membersCount' => $membersCount,
        ));
    }

    public function ajaxFindMembersAction(Request $request, $threadId)
    {
        $members = $this->_findPageMembers($request, $threadId);
        return $this->render('TopxiaWebBundle:Thread/Event:user-grids-li.html.twig', array(
            'members' => $members,
        ));
    }

    public function _findPageMembers($request, $threadId)
    {
        $page = $request->query->get('page', 0);
        $start = intval($page) * 16;
        $members = $this->getThreadService()->findMembersByThreadId($threadId, $start, 16);
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        foreach ($members as $key => $member) {
            $members[$key] = $users[$key];
        }
        return $members;
    }

    private function _findMyJoindedFriends($members)
    {
        $myFriends = $this->getUserService()->findAllUserFollowing($this->getCurrentUser()->id);
        $newFrinds = array();
        foreach ($myFriends as $key => $myFriend) {
            if (!empty($members[$key])) {
                $newFrinds[] = $myFriend;
            }
        }

        return $newFrinds;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}

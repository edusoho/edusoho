<?php
namespace Topxia\WebBundle\Controller\Thread;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\PHPExcelToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class MemberController extends BaseController
{
    public function becomeAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录!不能加入活动!');
        }

        if ($request->getMethod() == 'POST') {
            $data   = $request->request->all();
            $member = array(
                'threadId' => $threadId,
                'userId'   => $user['id'],
                'nickname' => $user['nickname'],
                'truename' => $data['truename'],
                'mobile'   => $data['mobile']
            );

            $member = $this->getThreadService()->createMember($member);

            return $this->createJsonResponse(empty($member) ? false : true);
        }

        $thread      = $this->getThreadService()->getThread($threadId);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        return $this->render('TopxiaWebBundle:Thread/Widget:user-info-modal.html.twig', array(
            'thread'      => $thread,
            'userProfile' => $userProfile
        ));
    }

    public function quitAction(Request $request, $memberId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('未登录,不能操作!');
        }

        $this->getThreadService()->deleteMember($memberId);

        return $this->createJsonResponse(true);
    }

    public function showMembersAction(Request $request, $thread)
    {
        $members      = $this->_findPageMembers($request, $thread['id']);
        $myFriends    = $this->_findMyJoindedFriends($members);
        $membersCount = $this->getThreadService()->findMembersCountByThreadId($thread['id']);

        return $this->render('TopxiaWebBundle:Thread/Event:user-grids.html.twig', array(
            'members'      => $members,
            'myFriends'    => $myFriends,
            'threadId'     => $thread['id'],
            'membersCount' => $membersCount
        ));
    }

    public function ajaxFindMembersAction(Request $request, $threadId)
    {
        $members = $this->_findPageMembers($request, $threadId);

        return $this->render('TopxiaWebBundle:Thread/Event:user-grids-li.html.twig', array(
            'members' => $members
        ));
    }

    public function exportAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户还未登录!');
        }

        $thread = $this->getThreadService()->getThread($threadId);

        if (empty($thread)) {
            return $this->createMessageResponse('warning', '帖子不存在!');
        }

        if (!$this->getThreadService()->canAccess('thread.update', $thread)) {
            throw $this->createAccessDeniedException('无权限操作!');
        }

        $filename   = $thread['title'].'-成员.xls';
        $members    = $this->_findMembersByThreadId($threadId);
        $execelInfo = $this->_makeInfo($user);
        $objWriter  = PHPExcelToolkit::export($members, $execelInfo);
        $this->_setHeader($filename);
        $objWriter->save('php://output');
    }

    protected function _makeInfo($user)
    {
        $title = array(
            'nickname'    => '用户名',
            'truename'    => '真实姓名',
            'mobile'      => '手机号码',
            'createdTime' => '报名时间'
        );
        $info              = array();
        $info['title']     = $title;
        $info['creator']   = $user['nickname'];
        $info['sheetName'] = '成员';
        return $info;
    }

    protected function _findMembersByThreadId($threadId)
    {
        $members = $this->getThreadService()->findMembersByThreadId($threadId, 0, PHP_INT_MAX);
        return $members;
    }

    protected function _findPageMembers($request, $threadId)
    {
        $page    = $request->query->get('page', 1);
        $start   = (intval($page) - 1) * 16;
        $members = $this->getThreadService()->findMembersByThreadId($threadId, $start, $start + 16);
        $userIds = ArrayToolkit::column($members, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        foreach ($members as $key => $member) {
            if (!empty($users[$key])) {
                $members[$key] = $users[$key];
            }
        }

        return $members;
    }

    protected function _findMyJoindedFriends($members)
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

    protected function _setHeader($filename)
    {
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$filename}");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}

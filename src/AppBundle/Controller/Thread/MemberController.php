<?php

namespace AppBundle\Controller\Thread;

use AppBundle\Controller\BaseController;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\PHPExcelToolkit;
use Biz\Thread\ThreadException;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class MemberController extends BaseController
{
    public function becomeAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $member = array(
                'threadId' => $threadId,
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'truename' => $data['truename'],
                'mobile' => $data['mobile'],
            );

            $member = $this->getThreadService()->createMember($member);

            return $this->createJsonResponse(empty($member) ? false : true);
        }

        $thread = $this->getThreadService()->getThread($threadId);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        return $this->render('thread/widget/user-info-modal.html.twig', array(
            'thread' => $thread,
            'userProfile' => $userProfile,
        ));
    }

    public function quitAction(Request $request, $memberId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $this->getThreadService()->deleteMember($memberId);

        return $this->createJsonResponse(true);
    }

    public function showMembersAction(Request $request, $thread)
    {
        $members = $this->_findPageMembers($request, $thread['id']);
        $myFriends = $this->_findMyJoindedFriends($members);

        $conditions = array('threadId' => $thread['id']);
        $membersCount = $this->getThreadService()->searchMemberCount($conditions);

        return $this->render('thread/event/user-grids.html.twig', array(
            'members' => $members,
            'myFriends' => $myFriends,
            'threadId' => $thread['id'],
            'membersCount' => $membersCount,
        ));
    }

    public function ajaxFindMembersAction(Request $request, $threadId)
    {
        $members = $this->_findPageMembers($request, $threadId);

        return $this->render('thread/event/user-grids-li.html.twig', array(
            'members' => $members,
        ));
    }

    public function exportAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $thread = $this->getThreadService()->getThread($threadId);

        if (empty($thread)) {
            return $this->createMessageResponse('warning', '帖子不存在!');
        }

        if (!$this->getThreadService()->canAccess('thread.update', $thread)) {
            $this->createNewException(ThreadException::ACCESS_DENIED());
        }

        $filename = $thread['title'].'-成员.xls';
        $members = $this->_findMembersByThreadId($threadId);
        $execelInfo = $this->_makeInfo($user);
        $objWriter = PHPExcelToolkit::export($members, $execelInfo);
        $this->_setHeader($filename);
        $objWriter->save('php://output');
    }

    protected function _makeInfo($user)
    {
        $title = array(
            'nickname' => '用户名',
            'truename' => '真实姓名',
            'mobile' => '手机号码',
            'createdTime' => '报名时间',
        );
        $info = array();
        $info['title'] = $title;
        $info['creator'] = $user['nickname'];
        $info['sheetName'] = '成员';

        return $info;
    }

    protected function _findMembersByThreadId($threadId)
    {
        $conditions = array('threadId' => $threadId);
        $members = $this->getThreadService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        return ArrayToolkit::index($members, 'userId');
    }

    protected function _findPageMembers($request, $threadId)
    {
        $page = $request->query->get('page', 1);
        $start = (intval($page) - 1) * 16;

        $conditions = array('threadId' => $threadId);

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $start,
            $start + 16
        );
        $members = ArrayToolkit::index($members, 'userId');
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

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
        return $this->getBiz()->service('Thread:ThreadService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}

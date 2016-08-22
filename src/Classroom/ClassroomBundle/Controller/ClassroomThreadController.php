<?php

namespace Classroom\ClassroomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ClassroomThreadController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');

        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && $member['locked'] == '0') {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }
        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace("/ /", "", $classroomDescription);
        }
        return $this->render('ClassroomBundle:ClassroomThread:list.html.twig', array(
            'classroom'            => $classroom,
            'filters'              => $this->getThreadSearchFilters($request),
            'canLook'              => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'service'              => $this->getThreadService(),
            'layout'               => $layout,
            'member'               => $member,
            'classroomDescription' => $classroomDescription
        ));
    }

    public function createAction(Request $request, $classroomId, $type)
    {
        if (!in_array($type, array('discussion', 'question', 'event'))) {
            throw $this->createAccessDeniedException('类型参数有误!');
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $request->getSession()->set('_target_path', $this->generateUrl('classroom_thread_create', array('classroomId' => $classroomId, 'type' => $type)));
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if ($type == 'event' && !$this->getClassroomService()->canCreateThreadEvent(array('targetId' => $classroomId))) {
            throw $this->createAccessDeniedException('无权限创建活动!');
        } elseif (in_array($type, array('discussion', 'question')) && !$this->getClassroomService()->canTakeClassroom($classroomId, true)) {
            throw $this->createAccessDeniedException('无权限创建话题!');
        }

        if ($request->getMethod() == 'POST') {
            return $this->forward('TopxiaWebBundle:Thread:create', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id'])));
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:ClassroomThread:create.html.twig', array(
            'classroom' => $classroom,
            'layout'    => $layout,
            'type'      => $type,
            'member'    => $member
        ));
    }

    public function updateAction(Request $request, $classroomId, $threadId)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (!$this->getClassroomService()->canLookClassroom($classroomId)) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }
        $thread = $this->getThreadService()->getThread($threadId);

        $user   = $this->getCurrentUser();
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        if ($request->getMethod() == 'POST') {
            return $this->forward('TopxiaWebBundle:Thread:update', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id']), 'thread' => $thread));
        }

        return $this->render('ClassroomBundle:ClassroomThread:create.html.twig', array(
            'classroom' => $classroom,
            'thread'    => $thread,
            'type'      => $thread['type'],
            'member'    => $member
        ));
    }

    public function showAction(Request $request, $classroomId, $threadId)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $thread    = $this->getThreadService()->getThread($threadId);
        $author    = $this->getUserService()->getUser($thread['userId']);
        $user      = $this->getCurrentUser();
        $adopted   = $request->query->get('adopted');
        $filter    = array();
        if (!empty($adopted)) {
            $filter = array('adopted' => $adopted);
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }
        if (empty($thread)) {
            return $this->createMessageResponse('error', '帖子已不存在');
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:ClassroomThread:show.html.twig', array(
            'classroom' => $classroom,
            'thread'    => $thread,
            'author'    => $author,
            'member'    => $member,
            'layout'    => $layout,
            'filter'    => $filter,
            'canLook'   => $this->getClassroomService()->canLookClassroom($classroom['id'])
        ));
    }

    private function getThreadSearchFilters($request)
    {
        $filters         = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'question', 'nice'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }

        return $filters;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}

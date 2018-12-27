<?php

namespace AppBundle\Controller\Classroom;

use Biz\Classroom\ClassroomException;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\Thread\Service\ThreadService;
use Biz\System\Service\SettingService;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomThreadController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $canLook = $this->getClassroomService()->canLookClassroom($classroom['id']);
        if (!$canLook) {
            $classroomName = $this->setting('classroom.name', '班级');

            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $user = $this->getCurrentUser();
        $member = $user->isLogin() ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = ($member && $member['locked'] == '0') ? 'classroom/join-layout.html.twig' : 'classroom/layout.html.twig';

        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace('/ /', '', $classroomDescription);
        }

        return $this->render('classroom-thread/list.html.twig', array(
            'classroom' => $classroom,
            'filters' => $this->getThreadSearchFilters($request),
            'canLook' => $canLook,
            'service' => $this->getThreadService(),
            'layout' => $layout,
            'member' => $member,
            'classroomDescription' => $classroomDescription,
        ));
    }

    public function createAction(Request $request, $classroomId, $type)
    {
        if (!in_array($type, array('discussion', 'question', 'event'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $request->getSession()->set('_target_path', $this->generateUrl('classroom_thread_create', array('classroomId' => $classroomId, 'type' => $type)));

            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if ($type == 'event' && !$this->getClassroomService()->canCreateThreadEvent(array('targetId' => $classroomId))) {
            $this->createNewException(ClassroomException::FORBIDDEN_CREATE_THREAD_EVENT());
        } elseif (in_array($type, array('discussion', 'question')) && !$this->getClassroomService()->canTakeClassroom($classroomId, true)) {
            $this->createNewException(ClassroomException::FORBIDDEN_TAKE_CLASSROOM());
        }

        if ($request->getMethod() == 'POST') {
            return $this->forward('AppBundle:Thread:create', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id'])));
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

        $layout = 'classroom/layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'classroom/join-layout.html.twig';
        }

        return $this->render('classroom-thread/create.html.twig', array(
            'classroom' => $classroom,
            'layout' => $layout,
            'type' => $type,
            'member' => $member,
        ));
    }

    public function updateAction(Request $request, $classroomId, $threadId)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $thread = $this->getThreadService()->getThread($threadId);

        if (empty($thread) || $thread['targetId'] != $classroomId) {
            return $this->createMessageResponse('error', "Thread#{$threadId} Not Found in Classroom#{$classroomId}");
        }

        $user = $this->getCurrentUser();
        $canManage = $this->canManageThread($user, $classroomId, $thread);

        if (!$canManage) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($request->isMethod('POST')) {
            return $this->forward('AppBundle:Thread:update', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id']), 'thread' => $thread));
        }

        return $this->render('classroom-thread/create.html.twig', array(
            'classroom' => $classroom,
            'thread' => $thread,
            'type' => $thread['type'],
            'member' => $member,
        ));
    }

    public function showAction(Request $request, $classroomId, $threadId)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $thread = $this->getThreadService()->getThread($threadId);

        if (empty($thread) || $thread['targetId'] != $classroomId) {
            return $this->createMessageResponse('error', "Thread#{$threadId} Not Found in Classroom#{$classroomId}");
        }

        $author = $this->getUserService()->getUser($thread['userId']);
        $user = $this->getCurrentUser();
        $adopted = $request->query->get('adopted');
        $filter = array();
        if (!empty($adopted)) {
            $filter = array('adopted' => $adopted);
        }

        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        $canLook = $this->getClassroomService()->canLookClassroom($classroom['id']);
        if (!$canLook) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }
        if (empty($thread)) {
            return $this->createMessageResponse('error', '帖子已不存在');
        }

        $layout = 'classroom/layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'classroom/join-layout.html.twig';
        }

        return $this->render('classroom-thread/show.html.twig', array(
            'classroom' => $classroom,
            'thread' => $thread,
            'author' => $author,
            'member' => $member,
            'layout' => $layout,
            'filter' => $filter,
            'canLook' => $canLook,
        ));
    }

    private function canManageThread($user, $classroomId, $thread)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->getClassroomService()->canManageClassroom($classroomId)) {
            return true;
        }

        if ($this->getClassroomService()->canTakeClassroom($classroomId, true) && $thread['userId'] == $user['id']) {
            return true;
        }

        return false;
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
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

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

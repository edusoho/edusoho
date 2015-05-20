<?php

namespace Classroom\ClassroomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ClassroomThreadController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && $member['locked'] == '0') {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:ClassroomThread:list.html.twig', array(
                'classroom' => $classroom,
                'filters' => $this->getThreadSearchFilters($request),
                'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
                'service' => $this->getThreadService(),
                'layout' => $layout,
                'member' => $member,
        ));
    }

    public function createAction(Request $request, $classroomId, $type)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if ($request->getMethod() == 'POST') {
            return $this->forward('TopxiaWebBundle:Thread:create', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id'])));
        }

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:ClassroomThread:create.html.twig', array(
            'classroom' => $classroom,
            'layout' => $layout,
            'type' => $type,
            'member' => $member,
        ));
    }

    public function updateAction(Request $request, $classroomId, $threadId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $thread = $this->getThreadService()->getThread($threadId);

        if ($request->getMethod() == 'POST') {
            return $this->forward('TopxiaWebBundle:Thread:update', array('request' => $request, 'target' => array('type' => 'classroom', 'id' => $classroom['id']), 'thread' => $thread));
        }

        return $this->render('ClassroomBundle:ClassroomThread:create.html.twig', array(
            'classroom' => $classroom,
            'thread' => $thread,
            'type' => $thread['type'],
        ));
    }

    public function showAction(Request $request, $classroomId, $threadId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $thread = $this->getThreadService()->getThread($threadId);
        $author = $this->getUserService()->getUser($thread['userId']);
        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        
        if (empty($thread)) {
            return $this->createMessageResponse('error', '帖子已不存在');
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:ClassroomThread:show.html.twig', array(
            'classroom' => $classroom,
            'thread' => $thread,
            'author' => $author,
            'member' => $member,
            'layout' => $layout,
            'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
        ));
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
}

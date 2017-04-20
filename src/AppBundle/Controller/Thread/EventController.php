<?php

namespace AppBundle\Controller\Thread;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class EventController extends BaseController
{
    public function showEventTitleAction(Request $request, $thread)
    {
        $user = $this->getCurrentUser();
        $member = $this->getThreadService()->getMemberByThreadIdAndUserId($thread['id'], $user['id']);

        return $this->render('thread/event/title-bar.html.twig', array(
            'thread' => $thread,
            'member' => $member,
            'author' => $this->getUserService()->getUser($thread['userId']),
        ));
    }

    public function otherEventsAction(Request $request, $threadId, $targetId, $targetType)
    {
        $conditions = array(
            'targetId' => $targetId,
            'targetType' => $targetType,
            'type' => 'event',
            'excludeIds' => array($threadId),
        );
        $threads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 5);

        return $this->render('thread/event/other-events-block.html.twig', array(
            'threads' => $threads,
        ));
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

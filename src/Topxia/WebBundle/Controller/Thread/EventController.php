<?php
namespace Topxia\WebBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class EventController extends BaseController
{

    public function showEventTitleAction(Request $request, $thread)
    {
        $user = $this->getCurrentUser();
        $member = $this->getThreadService()->getMemberByThreadIdAndUserId($thread['id'], $user['id']);

        return $this->render('TopxiaWebBundle:Thread/Event:title-bar.html.twig', array(
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

        return $this->render('TopxiaWebBundle:Thread/Event:other-events-block.html.twig', array(
            'threads' => $threads,
        ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
}

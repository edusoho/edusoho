<?php
namespace Topxia\WebBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityController extends BaseController
{

    public function showActivityTitleAction(Request $request, $thread)
    {
        $user = $this->getCurrentUser();
        $remainNum = $this->getThreadService()->remainMemberNum($thread);
        $member = $this->getThreadService()->getMemberByThreadIdAndUserId($thread['id'], $user['id']);

        return $this->render('TopxiaWebBundle:Thread/Activity:title-bar.html.twig', array(
            'thread' => $thread,
            'remainNum' => $remainNum,
            'member' => $member,
        ));
    }

    public function deleteAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->deleteThread($threadId);

        $user = $this->getCurrentUser();
        $userUrl = $this->generateUrl('user_show', array('id' => $user['id']), true);
        if ($thread['userId'] != $user['id']) {
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<strong>“{$thread['title']}”</strong>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>删除");
        }

        return $this->createJsonResponse(true);
    }

    public function otherActivitiesAction(Request $request, $threadId, $targetId, $targetType)
    {
        $conditions = array(
            'targetId' => $targetId,
            'targetType' => $targetType,
            'type' => 'activity',
            'excludeIds' => array($threadId),
        );
        $threads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 5);

        return $this->render('TopxiaWebBundle:Thread/Activity:other-activities-block.html.twig', array(
            'threads' => $threads,
        ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    private function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}

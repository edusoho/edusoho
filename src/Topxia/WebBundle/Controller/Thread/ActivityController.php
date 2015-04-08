<?php
namespace Topxia\WebBundle\Controller\Thread;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ActivityController extends BaseController
{

    public function showAction(Request $request, $target, $thread)
    {
        
        $conditions = array (
            'threadId'=>$thread['id'],
            'parentId'=>0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchPostsCount($conditions),
            20
        );

        $posts=$this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime','asc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $activityMembers = $this->getThreadService()->findActivityMembersByThreadId($thread['id']);

        $myFriends = $this->_findMyJoindedFriends($activityMembers);

        $userIds = array_unique(array_merge(ArrayToolkit::column($activityMembers, 'userId'), ArrayToolkit::column($posts, 'userId')));


        $users = $this->getUserService()->findUsersByIds($userIds);

        $this->getThreadService()->hitThread($target['id'], $thread['id']);

        return $this->render("TopxiaWebBundle:Thread/Activity:show.html.twig", array(
            'target' => $target,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
            'service' => $this->getThreadService(),
            'activityMembers' => $activityMembers,
            'myFriends' => $myFriends,
        ));
    }

    public function createAction(Request $request, $target, $thread = null)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['type'] = 'activity';
            $data['targetType'] = $target['type'];
            $data['targetId'] = $target['id'];
            $thread = $this->getThreadService()->createThread($data);
            return $this->redirect($this->generateUrl( "{$target['type']}_thread_activity_show", array(
               "{$target['type']}Id" => $thread['targetId'],
               'threadId' => $thread['id'],
            )));
        }

        return $this->render("TopxiaWebBundle:Thread/Activity:create.html.twig", array(
            'target' => $target,
            'thread' => $thread
        ));
    }

    public function updateAction(Request $request,  $target, $thread)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getCurrentUser();
            $thread = $this->getThreadService()->updateThread($thread['id'], $request->request->all());
            $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
            $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id" => $target['id'], 'threadId'=>$thread['id']), true);
            if ($thread['userId'] != $user['id']) {
                $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>编辑");
            }

            return $this->redirect($this->generateUrl("{$target['type']}_thread_show", array(
                "{$target['type']}Id" => $target['id'],
                'threadId' => $thread['id'],
            )));
        }

        return $this->render("TopxiaWebBundle:Thread:create.html.twig", array(
            'target' => $target,
            'thread' => $thread,
        ));
    }

    public function deleteAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->deleteThread($threadId);

        $user = $this->getCurrentUser();
        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
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
            'excludeIds' => array($threadId)
        );
        $threads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 5);
        
        return $this->render('TopxiaWebBundle:Thread/Activity:other-activities-block.html.twig' , array(
            'threads' => $threads
        ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    private function convertFiltersToConditions($id, $filters)
    {
        $conditions = array('targetId' => $id);
        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'nice':
                $conditions['nice'] = 1;
                break;
            default:
                break;
        }
        return $conditions;
    }

    private function _findMyJoindedFriends($activityMembers)
    {
        $myFriends = $this->getUserService()->findAllUserFollowing($this->getCurrentUser()->id);
        $newFrinds = array();
        foreach ($myFriends as $key => $myFriend) {
            if (!empty($activityMembers[$key])) {
                $newFrinds[] = $myFriend;
            }
        }

        return $newFrinds;
    }


    /**
     * This function is from Cakephp TextHelper Class
     */
    private function autoParagraph($text)
    {
        if (trim($text) !== '') {
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/\n\n+/", "\n\n", str_replace(array("\r\n", "\r"), "\n", $text));
            $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $text = '';
            foreach ($texts as $txt) {
                $text .= '<p>' . nl2br(trim($txt, "\n")) . "</p>\n";
            }
            $text = preg_replace('|<p>\s*</p>|', '', $text);
        }
        return $text;
    }

    private function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}
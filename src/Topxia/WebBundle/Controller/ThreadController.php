<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ThreadController extends BaseController
{

    public function listAction(Request $request, $target, $filters)
    {
        $conditions = $this->convertFiltersToConditions($target['id'], $filters);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'lastPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:Thread:list.html.twig", array(
            'target' => $target,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
        ));
    }

    public function showAction(Request $request, $target, $thread, $filter = array())
    {
        $conditions = array(
            'threadId' => $thread['id'],
            'parentId' => 0,
        );
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount(array_merge($conditions,$filter)),
            10
        );

        $posts = $this->getThreadService()->searchPosts(
            array_merge($conditions,$filter),
            array('createdTime', 'asc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $conditions['ups_GT'] = 5;
        $goodPosts = $this->getThreadService()->searchPosts(
            $conditions,
            array('ups' => 'DESC'),
            0,
            3
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column(array_merge($goodPosts,$posts), 'userId'));
        
        // $users = $this->getThreadService()->setUserBadgeTitle($thread, $users);
        $this->getThreadService()->hitThread($target['id'], $thread['id']);
        
        return $this->render("TopxiaWebBundle:Thread:show.html.twig", array(
            'target' => $target,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'goodPosts' => $goodPosts,
            'users' => $users,
            'paginator' => $paginator,
            'service' => $this->getThreadService(),
        ));
    }

    public function subpostsAction(Request $request, $threadId, $postId, $less = false)
    {
        $post = $this->getThreadService()->getPost($postId);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->findPostsCountByParentId($postId),
            10
        );

        $paginator->setBaseUrl($this->generateUrl('thread_post_subposts', array('threadId' => $post['threadId'], 'postId' => $postId)));

        $posts = $this->getThreadService()->findPostsByParentId($postId, $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        return $this->render('TopxiaWebBundle:Thread:subposts.html.twig', array(
            'parentId' => $postId,
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
            'less' => $less,
            'service' => $this->getThreadService(),
        ));
    }

    public function createAction(Request $request, $target, $type = 'discussion', $thread = null)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['targetType'] = $target['type'];
            $data['targetId'] = $target['id'];

            $thread = $this->getThreadService()->createThread($data);
            return $this->redirect($this->generateUrl("{$target['type']}_thread_show", array(
               "{$target['type']}Id" => $thread['targetId'],
               'threadId' => $thread['id'],
            )));
        }

        return $this->render("TopxiaWebBundle:Thread:create.html.twig", array(
            'target' => $target,
            'thread' => $thread,
            'type' => $type,
        ));
    }

    public function updateAction(Request $request, $target, $thread)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getCurrentUser();
            $data = $request->request->all();
            
            if(isset($data['maxUsers']) && empty($data['maxUsers'])) {
                $data['maxUsers'] = 0;
            }

            $thread = $this->getThreadService()->updateThread($thread['id'], $data);
               $message = array(
                'title' => $thread['title'],
                'targetType' => $target['type'],
                'targetId' => $target['id'],
                'type' => 'type-modify',
                'userId' => $user['id'],
                'userName' => $user['nickname']);

            if ($thread['userId'] != $user['id']) {
                $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
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
        if ($thread['userId'] != $user['id']) {
            $message = array(
                'title' => $thread['title'],
                'type' => 'delete',
                'userId' => $user['id'],
                'userName' => $user['nickname']);

            $this->getNotifiactionService()->notify($thread['userId'], 'group-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function setStickyAction(Request $request, $threadId)
    {
        $this->getThreadService()->setThreadSticky($threadId);

        return $this->createJsonResponse(true);
    }

    public function cancelStickyAction(Request $request, $threadId)
    {
        $this->getThreadService()->cancelThreadSticky($threadId);

        return $this->createJsonResponse(true);
    }

    public function setNiceAction(Request $request, $threadId)
    {
        $this->getThreadService()->setThreadNice($threadId);

        return $this->createJsonResponse(true);
    }

    public function cancelNiceAction(Request $request, $threadId)
    {
        $this->getThreadService()->cancelThreadNice($threadId);

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($threadId);
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['threadId'] = $threadId;

            $post = $this->getThreadService()->createPost($fields);
            // $authors = $this->getThreadService()->setUserBadgeTitle($thread, array($user['id'] => $user->toArray()));
            return $this->render('TopxiaWebBundle:Thread/Part:post-item.html.twig', array(
                'post' => $post,
                'author' => $user,
                'service' => $this->getThreadService(),
            ));
        }

        return $this->render("TopxiaWebBundle:Thread:post.html.twig", array(
            'thread' => $thread,
            'service' => $this->getThreadService(),
        ));
    }

    public function postReplyAction(Request $request, $threadId, $postId)
    {
        $fields = $request->request->all();
        $fields['content'] = $this->autoParagraph($fields['content']);
        $fields['threadId'] = $threadId;
        $fields['parentId'] = $postId;

        $post = $this->getThreadService()->createPost($fields);

        return $this->render('TopxiaWebBundle:Thread:subpost-item.html.twig', array(
            'post' => $post,
            'author' => $this->getCurrentUser(),
            'service' => $this->getThreadService(),
        ));
    }

    public function postDeleteAction(Request $request, $threadId, $postId)
    {
        $this->getThreadService()->deletePost($postId);

        return $this->createJsonResponse(true);
    }

    public function postUpAction(Request $request, $threadId, $postId)
    {
        $result = $this->getThreadService()->voteUpPost($postId);

        return $this->createJsonResponse($result);
    }

    public function jumpAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->generateUrl("{$thread['targetType']}_thread_show", array(
            "{$thread['targetType']}Id" => $thread['targetId'],
            'threadId' => $thread['id'],
        )));
    }

    public function postJumpAction(Request $request, $threadId, $postId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $post = $this->getThreadService()->getPost($postId);
        if ($post && $post['parentId']) {
            $post = $this->getThreadService()->getPost($post['parentId']);
        }

        if (empty($post)) {
            return $this->redirect($this->generateUrl("{$thread['targetType']}_thread_show", array(
                "{$thread['targetType']}Id" => $thread['targetId'],
                'threadId' => $thread['id'],
            )));
        }

        $position = $this->getThreadService()->getPostPostionInThread($post['id']);

        $page = ceil($position / 20);

        return $this->redirect($this->generateUrl("{$thread['targetType']}_thread_show", array(
            "{$thread['targetType']}Id" => $thread['targetId'],
            'threadId' => $thread['id'],
            'page' => $page,
        ))."#post-{$post['id']}");
    }

    public function userOtherThreadsBlockAction(Request $request, $thread, $userId)
    {
        $threads = $this->getThreadService()->findThreadsByTargetAndUserId(array('type' => $thread['targetType'], 'id' => $thread['targetId']), $userId, 0, 11);

        return $this->render('TopxiaWebBundle:Thread:user-threads-block.html.twig', array(
            'currentThread' => $thread,
            'threads' => $threads,
        ));
    }

    public function zeroPostThreadsBlockAction(Request $request, $thread)
    {
        $target = array('type' => $thread['targetType'], 'id' => $thread['targetId']);
        $threads = $this->getThreadService()->findZeroPostThreadsByTarget($target, 0, 11);

        return $this->render('TopxiaWebBundle:Thread:zero-post-threads-block.html.twig', array(
            'currentThread' => $thread,
            'threads' => $threads,
        ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function convertFiltersToConditions($id, $filters)
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

    /**
     * This function is from Cakephp TextHelper Class
     */
    protected function autoParagraph($text)
    {
        if (trim($text) !== '') {
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/\n\n+/", "\n\n", str_replace(array("\r\n", "\r"), "\n", $text));
            $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $text = '';
            foreach ($texts as $txt) {
                $text .= '<p>'.nl2br(trim($txt, "\n"))."</p>\n";
            }
            $text = preg_replace('|<p>\s*</p>|', '', $text);
        }

        return $text;
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}

<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ThreadController extends BaseController
{

    public function listAction(Request $request, $target, $filters)
    {
        $user = $this->getCurrentUser();

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

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($target['id'], $thread['id']);

        return $this->render("TopxiaWebBundle:Thread:show.html.twig", array(
            'target' => $target,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
            'service' => $this->getThreadService(),
        ));
    }

    public function subpostsAction(Request $request, $threadId, $postId, $less = false)
    {
        $thread = $this->getThreadService()->getThread($threadId);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->findPostsCountByParentId($postId),
            10
        );

        $paginator->setBaseUrl($this->generateUrl('thread_post_subposts', array('threadId' => $thread['id'], 'postId' => $postId)));

        $posts = $this->getThreadService()->findPostsByParentId($postId, $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        return $this->render('TopxiaWebBundle:Thread:subposts.html.twig', array(
            'parentId' => $postId,
            'thread' => $thread,
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
            'less' => $less,
            'service' => $this->getThreadService(),
        ));
    }


    public function createAction(Request $request, $target, $thread = null)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['type'] = 'discussion';
            $data['targetType'] = $target['type'];
            $data['targetId'] = $target['id'];
            $thread = $this->getThreadService()->createThread($data);
            return $this->redirect($this->generateUrl( "{$target['type']}_thread_show", array(
               "{$target['type']}Id" => $thread['targetId'],
               'threadId' => $thread['id'],
            )));
        }

        return $this->render("TopxiaWebBundle:Thread:create.html.twig", array(
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

    public function setStickyAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->setThreadSticky($threadId);

        return $this->createJsonResponse(true);
    }

    public function cancelStickyAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->cancelThreadSticky($threadId);

        return $this->createJsonResponse(true);
    }

    public function setNiceAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->setThreadNice($threadId);

        return $this->createJsonResponse(true);
    }

    public function cancelNiceAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->cancelThreadNice($threadId);

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $threadId)
    {
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['threadId'] = $threadId;

            $post = $this->getThreadService()->createPost($fields);

            return $this->render('TopxiaWebBundle:Thread:post-item.html.twig' , array(
                'post' => $post,
                'author' => $this->getCurrentUser(),
                'service' => $this->getThreadService(),
            ));
        }

        return $this->render("TopxiaWebBundle:Thread:post.html.twig", array(
            'thread' => $this->getThreadService()->getThread($threadId),
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

        return $this->render('TopxiaWebBundle:Thread:subpost-item.html.twig',array(
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
        if ($post and $post['parentId']) {
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
        )) . "#post-{$post['id']}");
    }

    public function userOtherThreadsBlockAction(Request $request, $thread, $userId)
    {
        $threads = $this->getThreadService()->findThreadsByTargetAndUserId(array('type' => $thread['targetType'], 'id' => $thread['targetId']), $userId, 0, 11);
        return $this->render('TopxiaWebBundle:Thread:user-threads-block.html.twig' , array(
            'currentThread' => $thread,
            'threads' => $threads,
        ));
    }

    public function zeroPostThreadsBlockAction(Request $request, $thread)
    {
        $target = array('type' => $thread['targetType'], 'id' => $thread['targetId']);
        $threads = $this->getThreadService()->findZeroPostThreadsByTarget($target, 0, 11);
        return $this->render('TopxiaWebBundle:Thread:zero-post-threads-block.html.twig' , array(
            'currentThread' => $thread,
            'threads' => $threads,
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
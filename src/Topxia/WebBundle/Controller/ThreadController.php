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
            ArrayToolkit::column($threads, 'lastPostMemberId')
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
        ));
    }

    public function subpostsAction(Request $request, $threadId, $postId)
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

        return $this->render("TopxiaWebBundle:Thread:subposts.html.twig", array(
            'thread' => $thread,
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
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
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>编辑");

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

    public function deleteAction(Request $request, $target, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);

        $this->getThreadService()->deleteThread($threadId);

        $user = $this->getCurrentUser();
        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='#' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>删除");

        return $this->createJsonResponse(true);
    }

    public function setStickyAction(Request $request, $target, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->setThreadSticky($threadId);
        $user = $this->getCurrentUser();

        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id"=> $target['id'],  'threadId' => $thread['id']), true);
        
        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>设为置顶");

        return $this->createJsonResponse(true);
    }

    public function cancelStickyAction(Request $request, $target, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->cancelThreadSticky($threadId);
        $user = $this->getCurrentUser();

        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id"=> $target['id'],  'threadId' => $thread['id']), true);

        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>取消置顶");


        return $this->createJsonResponse(true);
    }

    public function niceAction(Request $request, $target, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->setThreadNice($threadId);
        $user = $this->getCurrentUser();

        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id"=> $target['id'],  'threadId' => $thread['id']), true);

        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>加精");


        return $this->createJsonResponse(true);
    }

    public function cancelNiceAction(Request $request, $target, $threadId)
    {
        $thread = $this->getThreadService()->getThread($threadId);
        $this->getThreadService()->cancelThreadNice($threadId);
        $user = $this->getCurrentUser();

        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id"=> $target['id'],  'threadId' => $thread['id']), true);

        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>取消加精");


        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $target, $thread)
    {
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $postContent=$request->request->all();

            $fromUserId = empty($postContent['fromUserId']) ? 0 : $postContent['fromUserId'];
            $content=array(
            'content'=>$postContent['content'],'fromUserId'=>$fromUserId);

            if(isset($postContent['parentId'])){
                 $post=$this->getThreadService()->createPost($content,$target['type'], $target['id'], $user['id'], $thread['id'], $postContent['parentId']);
            }else{
                $post=$this->getThreadService()->createPost($content,$target['type'], $target['id'], $user['id'],$thread['id']);
            }

            $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
            $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id"=> $target['id'],  'threadId' => $thread['id']), true);
            $url=$this->getPost($target['type'] ,$post['id'],$thread['id'],$target['id']);


            if ($thread['userId'] != $user->id) {
                $this->getNotifiactionService()->notify($thread['userId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
            }

            if (empty($fromUserId) && !empty($postContent['postId'])) {
                $post = $this->getThreadService()->getPost($postContent['postId']);
                    if ($post['userId'] != $user->id && $post['userId'] != $thread['userId']) {
                        $this->getNotifiactionService()->notify($post['userId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$url}' target='_blank'>点击查看</a>");
                    }
            }

            if (!empty($fromUserId) && $fromUserId != $user->id && $fromUserId != $thread['userId']) {
                $this->getNotifiactionService()->notify($postContent['fromUserId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$url}' target='_blank'>点击查看</a>");
            }

            return new Response($url);
        }

        return $this->render("TopxiaWebBundle:Thread:post.html.twig", array(
            'target' => $target,
            'thread' => $thread,
        ));

    }

    private function getPost($targetType,$parentId,$threadId,$id)
    {   
        $post=$this->getThreadService()->getPost($id,$parentId);

        if($post['parentId']!=0)$parentId=$post['parentId'];
        $count=$this->getThreadService()->searchPostsCount(array('threadId'=>$threadId,'status'=>'open','id'=>$parentId,'parentId'=>0));

        $page=floor(($count)/30)+1;

        $url=$this->generateUrl( $targetType . '_thread_show',array("{$targetType}Id" => $id, 'threadId'=>$threadId));

        $url=$url."?page=$page#post-$parentId";
        return $url;
    }


    public function postReplyAction(Request $request,$targetType,$parentId)
    {   
        $postReplyAll=array();

        $replyCount=$this->getThreadService()->searchPostsCount(array('parentId'=>$parentId));

        $postReplyPaginator = new Paginator(
                $this->get('request'),
                $replyCount,
                10  
            );

        $postReply=$this->getThreadService()->searchPosts(array('parentId'=>$parentId),array('createdTime','asc'),
                $postReplyPaginator->getOffsetCount(),
                $postReplyPaginator->getPerPageCount());

        if($postReply){
                $postReplyAll=array_merge($postReplyAll,ArrayToolkit::column($postReply, 'userId'));
        }
        $postReplyMembers=$this->getUserService()->findUsersByIds($postReplyAll);
        return $this->render('TopxiaWebBundle:Thread:thread-reply-list.html.twig',array(
            'postMain' => array('id'=>$parentId),
            'postReply'=>$postReply,
            'postReplyMembers'=>$postReplyMembers,
            'postReplyCount'=>$replyCount,
            'postReplyPaginator'=>$postReplyPaginator,
            'targetType' =>$targetType,
            ));
    }

    public function postUpdateAction(Request $request, $target, $thread, $post)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {

            $post = $this->getThreadService()->updatePost($post['id'], $request->request->all());

            // $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");
            // $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");

            return $this->redirect($this->generateUrl("{$target['type']}_thread_show", array(
                "{$target['type']}Id" => $target['id'],
                'threadId' => $post['threadId'],
            )));
        }

        return $this->render('TopxiaWebBundle:Thread:post-update.html.twig', array(
            'target' => $target,
            'thread' => $thread,
            'post' => $post,
        ));

    }

    public function postDeleteAction(Request $request, $target, $thread, $post)
    {
        $this->getThreadService()->deletePost($post['id'],$thread['id']);
        $user = $this->getCurrentUser();

        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        $threadUrl = $this->generateUrl("{$target['type']}_thread_show", array("{$target['type']}Id" => $target['id'], 'threadId'=>$thread['id']), true);

        $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>删除。");
        $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>删除。");

        return $this->createJsonResponse(true);
    }

    public function questionBlockAction(Request $request, $course)
    {
        $threads = $this->getThreadService()->searchThreads(
            array('courseId' => $course['id'], 'type'=> 'question'),
            'createdNotStick',
            0,
            8
        );

        return $this->render('TopxiaWebBundle:CourseThread:question-block.html.twig', array(
            'course' => $course,
            'threads' => $threads,
        ));
    }

    private function createPostForm($data = array())
    {
        return $this->createNamedFormBuilder('post', $data)
            ->add('content', 'textarea')
            ->add('targetId', 'hidden')
            ->add('threadId', 'hidden')
            ->add('targetType', 'hidden')
            ->add('parentId', 'hidden')
            ->getForm();
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
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }
        return $conditions;
    }

    private function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    } 

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
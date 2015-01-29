<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ThreadController extends BaseController
{
    private function checkTargetType($id,$targetType)
    {           
        switch ($targetType) {
            case 'classroom':
                $classroom = $this->getClassroomService()->getClassroom($id);
                if (empty($classroom)) {
                    throw $this->createNotFoundException("班级不存在，或已删除。");
                }

                if (!$this->getClassroomService()->canTakeClassroom($classroom)) {
                    // return $this->createMessageResponse('info', "您还不是班级《{$classroom['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
                    return $this->createMessageResponse('info', "您还不是班级《{$classroom['title']}》的学员，请先购买或加入学习。", null, 3000, null);             
                }

                 $result = $this->getClassroomService()->tryTakeClassroom($id);
                break;
            default:

                throw $this->createNotFoundException('参数targetType不正确。');
        }
        return $result;

    }

    public function indexAction(Request $request, $id, $targetType)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $this->checkTargetType($id,$targetType);

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($id, $filters);

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
// var_dump($conditions);
// var_dump($threads);exit();
        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'lastPostMemberId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        $template = $request->isXmlHttpRequest() ? 'index-main' : 'index';

        return $this->render("TopxiaWebBundle:Thread:{$template}.html.twig", array(
            'id' => $id,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
            'targetType' =>$targetType,
            'sort'=>'posted',
            'type'=>'all',
        ));
    }

    public function showAction(Request $request, $targetId, $id,$targetType)
    {

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

         list($classroom,$member) = $this->checkTargetType($targetId,$targetType);
         if (empty($member)) {
            // return $this->createMessageResponse('info', "您还不是班级《{$classroom['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $targetId)));
            return $this->createMessageResponse('info', "您还不是班级《{$classroom['title']}》的学员，请先购买或加入学习。", null, 3000, null);
         }


        $thread = $this->getThreadService()->getThread($targetId, $id);

        if (empty($thread)) {
            throw $this->createNotFoundException("话题不存在，或已删除。");
        }

        // if($request->query->get('post'))
        // {   
        //     $url=$this->getPost($targetType,$request->query->get('post'),$targetId,$id);
            
        //     return $this->redirect($url);
        // }
        
        $condition=array('threadId'=>$thread['id'],'status'=>'open','parentId'=>0);
        $postCount=$this->getThreadService()->searchPostsCount($condition);

        $paginator = new Paginator(
            $this->get('request'),
            $postCount,
            30  
        );

        $posts=$this->getThreadService()->searchPosts($condition,array('createdTime','asc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $postMemberIds = ArrayToolkit::column($posts, 'userId');
        $postId=ArrayToolkit::column($posts, 'id');
        $elitePosts = array();
        $postReplyAll=array();
        $postReply=array();
        $postReplyCount=array();
        $postReplyPaginator=array();
        $isManager = '';
        $replyPaginator='';

        foreach ($postId as $key => $value) {
            $replyCount=$this->getThreadService()->searchPostsCount(array('parentId'=>$value));
            $replyPaginator = new Paginator(
                $this->get('request'),
                $replyCount,
                10  
            );

            $reply=$this->getThreadService()->searchPosts(array('parentId'=>$value),array('createdTime','asc'),
                $replyPaginator->getOffsetCount(),
                $replyPaginator->getPerPageCount());

            $postReplyCount[$value]=$replyCount;
            $postReply[$value]=$reply;
            $postReplyPaginator[$value]=$replyPaginator;

            if($reply){
                $postReplyAll=array_merge($postReplyAll,ArrayToolkit::column($reply, 'userId'));
            }

        }

        $postReplyMembers=$this->getUserService()->findUsersByIds($postReplyAll);
        $postMember=$this->getUserService()->findUsersByIds($postMemberIds);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($targetId, $id);

        if ($targetType == 'classroom') {
            $isManager = $this->getClassroomService()->canManageClassroom($targetId);
        }

        return $this->render("TopxiaWebBundle:Thread:show.html.twig", array(
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'elitePosts' => $elitePosts,
            'users' => $users,
            'isManager' => $isManager,
            'paginator' => $paginator,
            'targetType' =>$targetType,
            'targetId' => $targetId,
            'postCount' =>$postCount,
            'postMember'=>$postMember,
            'postReply'=>$postReply,
            'postReplyMembers'=>$postReplyMembers,
            'postReplyCount'=>$postReplyCount,
            'postReplyPaginator'=>$postReplyPaginator,
            'replyPaginator'=>$replyPaginator,
            'sort'=>'posted',
            'type'=>'all',
            'member'=>$member,
        ));
    }


    public function createAction(Request $request, $id, $targetType)
    {
        // list($classroom,$member) = $this->checkTargetType($id,$targetType);
        
        // if ($member && $member['levelId'] > 0) {
        //     if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
        //         return $this->redirect($this->generateUrl('course_show',array('id' => $id)));
        //     }
        // }
        
        $this->checkTargetType($id,$targetType);
        $type = $request->query->get('type') ? : 'discussion';
        $form = $this->createThreadForm(array(
            'type' => $type,
            'targetId' => $id,
            'targetType'=>$targetType,
        ));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $thread = $this->getThreadService()->createThread($form->getData());
                return $this->redirect($this->generateUrl('thread_show', array(
                   'targetId' => $thread['targetId'],
                   'id' => $thread['id'], 
                   'targetType'=>$targetType,
                )));
            }
        }

        return $this->render("TopxiaWebBundle:Thread:form.html.twig", array(
            'targetId' => $id,
            'form' => $form->createView(),
            'type' => $type,
            'targetType'=>$targetType,
            'sort'=>'posted',
            'type'=>'all',
        ));
    }

    public function editAction(Request $request,  $targetType, $targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        // if ($user->isLogin() and $user->id == $thread['userId']) {
        //     $course = $this->getCourseService()->getCourse($courseId);
        // } else {
        //     $course = $this->getCourseService()->tryManageCourse($courseId);
        // }

        $form = $this->createThreadForm($thread);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $thread = $this->getThreadService()->updateThread($thread['targetId'], $thread['id'], $form->getData());
                
                if ($user->isAdmin()) {             
                    $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$thread['id'],'targetType'=>$targetType), true);
                    $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员编辑");
                }

                return $this->redirect($this->generateUrl('thread_show', array(
                   'targetId' => $thread['targetId'],
                   'id' => $thread['id'], 
                   'targetType'=>$targetType,
                )));
            }
        }

        return $this->render("TopxiaWebBundle:Thread:form.html.twig", array(
            'form' => $form->createView(),
            'targetId' => $targetId,
            'thread' => $thread,
            'type' => $thread['type'],
            'targetType'=>$targetType,
            'sort'=>'posted',
            'type'=>'all',
        ));

    }

    private function createThreadForm($data = array())
    {
        return $this->createNamedFormBuilder('thread', $data)
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('type', 'hidden')
            ->add('targetId', 'hidden')
            ->add('targetType', 'hidden')
            ->getForm();
    }

    public function latestBlockAction($course)
    {
    	$threads = $this->getThreadService()->searchThreads(array('courseId' => $course['id']), 'createdNotStick', 0, 10);

    	return $this->render('TopxiaWebBundle:CourseThread:latest-block.html.twig', array(
    		'course' => $course,
            'threads' => $threads,
		));
    }

    public function deleteAction(Request $request, $targetType, $targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        $this->getThreadService()->deleteThread($targetType,$id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员删除");
        }

        return $this->createJsonResponse(true);
    }

    public function stickAction(Request $request,$targetType, $targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        $this->getThreadService()->stickThread($targetType,$targetId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员设为置顶");
        }

        return $this->createJsonResponse(true);
    }

    public function unstickAction(Request $request, $targetType,$targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        $this->getThreadService()->unstickThread($targetType,$targetId, $id);
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员取消置顶");
        }

        return $this->createJsonResponse(true);
    }

    public function eliteAction(Request $request, $targetType,$targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        $this->getThreadService()->eliteThread($targetType,$targetId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员加精");
        }

        return $this->createJsonResponse(true);
    }

    public function uneliteAction(Request $request, $targetType,$targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        $this->getThreadService()->uneliteThread($targetType,$targetId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员取消加精");
        }

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $targetType,$targetId, $id)
    {
        // list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $user=$this->getCurrentUser();
        if (!$user->isLogin()) {
        return new Response($this->generateUrl('login'));
        }

        $thread = $this->getThreadService()->getThread($targetId, $id);

        $postContent=$request->request->all();

        $targetId = empty($postContent['targetId']) ? $targetId : $postContent['targetId'];
        $content=array('content'=>$postContent['content'],'targetId'=>$targetId);
        // var_dump($postContent);exit();

        if(isset($postContent['parentId'])){

             $post=$this->getThreadService()->createPost($content,$targetId,$user['id'],$id,$postContent['parentId']);

        }else{

            $post=$this->getThreadService()->createPost($content,$targetId,$user['id'],$id);

        }       

        $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);
        $url=$this->getPost($targetType,$post['id'],$thread['id'],$targetId);

         if ($thread['userId'] != $user->id) {
                $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
                $this->getNotifiactionService()->notify($thread['userId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$user['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
        }

        $content=$postContent['content'];
        $users=array();
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $content, $matches);
        $mentions = array_unique($matches[1]);
   
        foreach ($mentions as $mention) {
            
            $user=$this->getUserService()->getUserByNickname($mention);
            
            if($user){

                $path = $this->generateUrl('user_show', array('id' => $user['id']));
                $nickname=$user['nickname'];
                $html = "<a href=\"{$path}\" class=\"show-user\">@{$nickname}</a>";

                $content = preg_replace("/@{$nickname}/ui", $html, $content);

                $users[]=$user;
            }
         
        }

            foreach ($users as $user) {
                if ($thread['userId'] != $user['id']) {
                    if ($user['id'] != $userId) {
                    $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
                    $this->getNotifiactionService()->notify($user['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中@了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
                    }
                }
            }

            return new Response($url);

        // $form = $this->createPostForm(array(
        //     'targetId' => $thread['targetId'],
        //     'threadId' => $thread['id'],
        //     'targetType'=>$targetType,
        //     'parentId'=>0,
        // ));

        // $currentUser = $this->getCurrentUser();
        // if ($request->getMethod() == 'POST') {
        //     $form->bind($request);
        //     $userId = $currentUser->id;
        //     if ($form->isValid()) {
        //         $postData=$form->getData();
                
        //         list($postData,$users)=$this->replaceMention($postData);
                
        //         if(isset($postData['parentId'])){
        //             $post=$this->getThreadService()->createPost($postData,$postData['parentId']);

        //         }else{
        //             $post = $this->getThreadService()->createPost($postData);

        //         }

        //         $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$id,'targetType'=>$targetType), true);

        //         $threadUrl .= "#post-". $post['id'];

        //         $url=$this->getPost($targetType,$post['id'],$thread['id'],$targetId);

        //         if ($thread['userId'] != $currentUser->id) {

        //             $userUrl = $this->generateUrl('user_show', array('id'=>$userId), true);
                    
        //             $this->getNotifiactionService()->notify($thread['userId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
        //         }

        //         foreach ($users as $user) {
        //             if ($thread['userId'] != $user['id']) {
        //                 if ($user['id'] != $userId) {
        //                 $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
        //                 $this->getNotifiactionService()->notify($user['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中@了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
        //                 }
        //             }
        //         }

        //         $isManager = '';

        //         if ($targetType == 'classroom') {
        //             $isManager = $this->getClassroomService()->canManageClassroom($targetId);
        //         }

        //         return new Response($url);

        //     } else {
        //         return $this->createJsonResponse(false);
        //     }
        // }

        // return $this->render('TopxiaWebBundle:Thread:post.html.twig', array(
        //     'targetId' => $targetId,
        //     'thread' => $thread,
        //     'form' => $form->createView(),
        //     'targetType'=>$targetType,
        // ));
    }

    private function getPost($targetType,$parentId,$threadId,$id)
    {   
        $post=$this->getThreadService()->getPost($id,$parentId);

        if($post['parentId']!=0)$parentId=$post['parentId'];
        $count=$this->getThreadService()->searchPostsCount(array('threadId'=>$threadId,'status'=>'open','id'=>$parentId,'parentId'=>0));

        $page=floor(($count)/30)+1;

        $url=$this->generateUrl('thread_show',array('targetId'=>$id,'id'=>$threadId,'targetType'=>$targetType));

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

    private function replaceMention($postData)
    {   
        $currentUser = $this->getCurrentUser();
        $content=$postData['content'];
        $users=array();
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $content, $matches);
        $mentions = array_unique($matches[1]);
   
        foreach ($mentions as $mention) {
            
            $user=$this->getUserService()->getUserByNickname($mention);
            
            if($user){

                $path = $this->generateUrl('user_show', array('id' => $user['id']));
                $nickname=$user['nickname'];
                $html = "<a href=\"{$path}\" class=\"show-user\">@{$nickname}</a>";

                $content = preg_replace("/@{$nickname}/ui", $html, $content);

                $users[]=$user;
            }
         
        }
     
        $postData['content']=$content;
    
        return array($postData,$users);
        
    }

    public function editPostAction(Request $request, $targetType,$targetId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($targetId, $id);
        if (empty($post)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        // if ($user->isLogin() and $user->id == $post['userId']) {
        //     $course = $this->getCourseService()->getCourse($targetId);
        // } else {
        //     $course = $this->getCourseService()->tryManageCourse($targetId);
        // }

        $thread = $this->getThreadService()->getThread($targetId, $threadId);

        $form = $this->createPostForm($post);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['targetId'], $post['id'], $form->getData());
                if ($user->isAdmin()) {
                    $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$threadId,'targetType'=>$targetType), true);
                    $threadUrlAnchor = $threadUrl."#post-".$id;
                    $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");
                    $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");
                }
                return $this->redirect($this->generateUrl('thread_show', array(
                    'targetId' => $post['targetId'],
                    'id' => $post['threadId'],
                    'targetType'=>$targetType
                )));
            }
        }

        return $this->render('TopxiaWebBundle:Thread:post-form.html.twig', array(
            'targetId' => $targetId,
            'form' => $form->createView(),
            'post' => $post,
            'thread' => $thread,
            'targetType'=>$targetType,
            'sort'=>'posted',
            'type'=>'all',
        ));

    }

    public function deletePostAction(Request $request, $targetType,$targetId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($targetId, $id);
        $this->getThreadService()->deletePost($targetType,$targetId, $id);
        $user = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($targetId, $threadId);

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$threadId,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员删除。");
            $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员删除。");
        }

        if ($thread['userId'] == $user['id']) {
            $threadUrl = $this->generateUrl('thread_show', array('targetId'=>$targetId,'id'=>$threadId,'targetType'=>$targetType), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被话题作者删除。");
            $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被话题作者删除。");
        }

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

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'question', 'elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
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
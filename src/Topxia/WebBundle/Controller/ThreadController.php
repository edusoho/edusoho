<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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

                // if (!$this->getClassroomService()->canTakeClassroom($classroom)) {
                //     return $this->createMessageResponse('info', "您还不是班级《{$classroom['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
                // }

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
            'targetType' =>$targetType
        ));
    }

    public function showAction(Request $request, $targetId, $id,$targetType)
    {

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }


        $thread = $this->getThreadService()->getThread($targetId, $id);
        if (empty($thread)) {
            throw $this->createNotFoundException("话题不存在，或已删除。");
        }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->getThreadPostCount($targetId, $thread['id']),
            30
        );

        $posts = $this->getThreadService()->findThreadPosts(
            $thread['targetId'],
            $thread['id'],
            'default',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $elitePosts = array();
        $isManager = '';

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
            'id' => $id,
            'form' => $form->createView(),
            'type' => $type,
            'targetType'=>$targetType,
        ));
    }

    public function editAction(Request $request, , $targetType, $targetId, $id)
    {
        $thread = $this->getThreadService()->getThread($targetId, $id);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin() and $user->id == $thread['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

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
        ));

    }

    private function createThreadForm($data = array())
    {
        return $this->createNamedFormBuilder('thread', $data)
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('type', 'hidden')
            ->add('targetId', 'hidden')
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
        $this->getThreadService()->stickThread($targetType,$courseId, $id);
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

    public function postAction(Request $request, $courseId, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $thread = $this->getThreadService()->getThread($course['id'], $id);
        $form = $this->createPostForm(array(
            'courseId' => $thread['courseId'],
            'threadId' => $thread['id']
        ));

        $currentUser = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $userId = $currentUser->id;
            if ($form->isValid()) {
                $postData=$form->getData();
                
                list($postData,$users)=$this->replaceMention($postData);
             
                $post = $this->getThreadService()->createPost($postData);

                $threadUrl = $this->generateUrl('course_thread_show', array('courseId'=>$courseId,'id'=>$id), true);
                $threadUrl .= "#post-". $post['id'];

                if ($thread['userId'] != $currentUser->id) {

                    $userUrl = $this->generateUrl('user_show', array('id'=>$userId), true);
                    
                    $this->getNotifiactionService()->notify($thread['userId'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中回复了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
                }

                foreach ($users as $user) {
                    if ($thread['userId'] != $user['id']) {
                        if ($user['id'] != $userId) {
                        $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
                        $this->getNotifiactionService()->notify($user['id'], 'default', "<a href='{$userUrl}' target='_blank'><strong>{$currentUser['nickname']}</strong></a>在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>中@了您。<a href='{$threadUrl}' target='_blank'>点击查看</a>");
                        }
                    }
                }

                return $this->render('TopxiaWebBundle:CourseThread:post-list-item.html.twig', array(
                    'course' => $course,
                    'thread' => $thread,
                    'post' => $post,
                    'author' => $this->getUserService()->getUser($post['userId']),
                    'isManager' => $this->getCourseService()->canManageCourse($course['id'])
                ));
            } else {
                return $this->createJsonResponse(false);
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post.html.twig', array(
            'course' => $course,
            'thread' => $thread,
            'form' => $form->createView()
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

    public function editPostAction(Request $request, $courseId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($courseId, $id);
        if (empty($post)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin() and $user->id == $post['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        $form = $this->createPostForm($post);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['courseId'], $post['id'], $form->getData());
                if ($user->isAdmin()) {
                    $threadUrl = $this->generateUrl('course_thread_show', array('courseId'=>$courseId,'id'=>$threadId), true);
                    $threadUrlAnchor = $threadUrl."#post-".$id;
                    $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");
                    $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员编辑。<a href='{$threadUrlAnchor}' target='_blank'>点击查看</a>");
                }
                return $this->redirect($this->generateUrl('course_thread_show', array(
                    'courseId' => $post['courseId'],
                    'id' => $post['threadId']
                )));
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post-form.html.twig', array(
            'course' => $course,
            'form' => $form->createView(),
            'post' => $post,
            'thread' => $thread,
        ));

    }

    public function deletePostAction(Request $request, $courseId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($courseId, $id);
        $this->getThreadService()->deletePost($courseId, $id);
        $user = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId'=>$courseId,'id'=>$threadId), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'default', "您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员删除。");
            $this->getNotifiactionService()->notify($post['userId'], 'default', "您在话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>有回复被管理员删除。");
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
            ->add('courseId', 'hidden')
            ->add('threadId', 'hidden')
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
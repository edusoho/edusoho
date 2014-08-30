<?php

namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class GroupThreadController extends BaseController 
{
    public function addThreadAction(Request $request,$id)
    {
        $user=$this->getCurrentUser();

        $groupinfo = $this->getGroupService()->getGroup($id);
        if(!$groupinfo){
            return $this->createMessageResponse('info','该小组已被关闭');
        }
        if(!$this->getGroupMemberRole($id)){
            return $this->createMessageResponse('info','只有小组成员可以发言');
        }

        if($request->getMethod()=="POST"){
            $thread = $request->request->all();

            $title=trim($thread['thread']['title']);
            if(empty($title)){
                $this->setFlashMessage('danger',"话题名称不能为空！");

                return $this->render('TopxiaWebBundle:Group:add-thread.html.twig',
                    array(
                    'id'=>$id,
                    'groupinfo'=>$groupinfo,
                    'is_groupmember' => $this->getGroupMemberRole($id)
                    ));
            }

            $info=array(
                'title'=>$thread['thread']['title'],
                'content'=>$thread['thread']['content'],
                'groupId'=>$id,
                'userId'=>$user['id']);
            $thread=$this->getThreadService()->addThread($info);
                
            return $this->redirect($this->generateUrl('group_thread_show', array(
                'id'=>$id,
                'threadId'=>$thread['id'],
                )));
            
        }
        return $this->render('TopxiaWebBundle:Group:add-thread.html.twig',
            array(
            'id'=>$id,
            'groupinfo'=>$groupinfo,
            'is_groupmember' => $this->getGroupMemberRole($id)
            ));
    }

    public function updateThreadAction(Request $request,$id,$threadId)
    {
        $user=$this->getCurrentUser();
        $groupinfo = $this->getGroupService()->getGroup($id);
        if(!$groupinfo){
            return $this->createMessageResponse('info','该小组已被关闭');
        }

        $thread=$this->getThreadService()->getThread($threadId);
        if($thread['userId']!==$user['id'] && $this->getGroupMemberRole($id)!==3 && $this->getGroupMemberRole($id)!==2 && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true){
            return $this->createMessageResponse('info','您没有权限编辑');
        }

        $thread=$this->getThreadService()->getThread($threadId);

        if($request->getMethod()=="POST"){
        $thread = $request->request->all();
        $fields=array(
                'title'=>$thread['thread']['title'],
                'content'=>$thread['thread']['content'],);
        $thread=$this->getThreadService()->updateThread($threadId,$fields);
            
        return $this->redirect($this->generateUrl('group_thread_show', array(
            'id'=>$id,
            'threadId'=>$threadId,
            )));
            
        }

        return $this->render('TopxiaWebBundle:Group:add-thread.html.twig',array(
            'id'=>$id,
            'groupinfo'=>$groupinfo,
            'thread'=>$thread,
            'is_groupmember' => $this->getGroupMemberRole($id)));
    }

    public function checkUserAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);

        if ($result) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function groupThreadIndexAction(Request $request,$id,$threadId)
    {  
        $group = $this->getGroupService()->getGroup($id);

        if($group['status']=="close"){
            return $this->createMessageResponse('info','该小组已被关闭');
        }

        $user=$this->getCurrentUser();

        $threadMain=$this->getThreadService()->getThread($threadId);

        if($threadMain['status']=="close"){
            return $this->createMessageResponse('info','该话题已被关闭');
        }

        $this->getThreadService()->waveHitNum($threadId);

        if($request->query->get('post'))
        {   
            $url=$this->getPost($request->query->get('post'),$threadId,$id);
            
            return $this->redirect($url);
        }

        $owner=$this->getUserService()->getUser($threadMain['userId']);

        $filters=$this->getPostSearchFilters($request);

        $condition=$this->getPostCondition($filters['type'],$threadMain['userId'],$threadId);

        $sort=$this->getPostOrderBy($filters['sort']);

        $postCount=$this->getThreadService()->searchPostsCount($condition);

        $paginator = new Paginator(
            $this->get('request'),
            $postCount,
            30  
        );

        $post=$this->getThreadService()->searchPosts($condition,$sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $postMemberIds = ArrayToolkit::column($post, 'userId');

        $postId=ArrayToolkit::column($post, 'id');

        $postReplyAll=array();
        $postReply=array();
        $postReplyCount=array();
        $postReplyPaginator=array();
        foreach ($postId as $key => $value) {

            $replyCount=$this->getThreadService()->searchPostsCount(array('postId'=>$value));
            $replyPaginator = new Paginator(
                $this->get('request'),
                $replyCount,
                10  
            );

            $reply=$this->getThreadService()->searchPosts(array('postId'=>$value),array('createdTime','asc'),
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

        $activeMembers=$this->getGroupService()->searchMembers(array('groupId'=>$id),
            array('postNum','DESC'),0,12);

        $memberIds = ArrayToolkit::column($activeMembers, 'userId');
        $members=$this->getUserService()->findUsersByIds($memberIds);

        $groupShareContent="";
        $defaultSetting = $this->getSettingService()->get('default', array());
        if(isset($defaultSetting['groupShareContent'])){
            $groupShareContent = str_replace("{{groupname}}", $group['title'], $defaultSetting['groupShareContent']);
            $groupShareContent = str_replace("{{threadname}}", $threadMain['title'], $groupShareContent);
        }
        
        return $this->render('TopxiaWebBundle:Group:thread.html.twig',array(
            'groupinfo' => $group,
            'groupShareContent'=>$groupShareContent,
            'threadMain'=>$threadMain,
            'user'=>$user,
            'owner'=>$owner,
            'post'=>$post,
            'paginator'=>$paginator,
            'postMember'=>$postMember,
            'filters'=>$filters,
            'postCount'=>$postCount,
            'postReply'=>$postReply,
            'activeMembers'=>$activeMembers,
            'postReplyMembers'=>$postReplyMembers,
            'members'=>$members,
            'postReplyCount'=>$postReplyCount,
            'postReplyPaginator'=>$postReplyPaginator,
            'is_groupmember' => $this->getGroupMemberRole($id)));
    }

    public function postReplyAction(Request $request,$postId)
    {   
        $postReplyAll=array();

        $replyCount=$this->getThreadService()->searchPostsCount(array('postId'=>$postId));

        $postReplyPaginator = new Paginator(
                $this->get('request'),
                $replyCount,
                10  
            );

        $postReply=$this->getThreadService()->searchPosts(array('postId'=>$postId),array('createdTime','asc'),
                $postReplyPaginator->getOffsetCount(),
                $postReplyPaginator->getPerPageCount());

        if($postReply){
                $postReplyAll=array_merge($postReplyAll,ArrayToolkit::column($postReply, 'userId'));
        }
        $postReplyMembers=$this->getUserService()->findUsersByIds($postReplyAll);
        return $this->render('TopxiaWebBundle:Group:thread-reply-list.html.twig',array(
            'postMain' => array('id'=>$postId),
            'postReply'=>$postReply,
            'postReplyMembers'=>$postReplyMembers,
            'postReplyCount'=>$replyCount,
            'postReplyPaginator'=>$postReplyPaginator,
            ));
    }

    public function postThreadAction(Request $request,$groupId,$threadId)
    {       
            $user=$this->getCurrentUser();
            if (!$user->isLogin()) {
            return new Response($this->generateUrl('login'));
            }

            if(!$this->getGroupMemberRole($groupId)){
            $this->getGroupService()->joinGroup($user,$groupId);
            }

            $postContent=$request->request->all();
            $content=array(
            'content'=>$postContent['content'],);

            if(isset($postContent['postId'])){

                 $post=$this->getThreadService()->postThread($content,$groupId,$user['id'],$threadId,$postContent['postId']);

            }else{

                 $post=$this->getThreadService()->postThread($content,$groupId,$user['id'],$threadId);

            }       

            $url=$this->getPost($post['id'],$threadId,$groupId);
            return new Response($url);

    }
    
    public function searchResultAction(Request $request,$id)
    {   
        $keyWord=$request->query->get('keyWord') ? : "";
        $group = $this->getGroupService()->getGroup($id);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount(array('status'=>'open','title'=>$keyWord,'groupId'=>$id)),
            15  
        );
        $threads=$this->getThreadService()->searchThreads(array('status'=>'open','title'=>$keyWord,
            'groupId'=>$id),
            array(array('createdTime','DESC')), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $ownerIds = ArrayToolkit::column($threads, 'userId');

        $userIds = ArrayToolkit::column($threads, 'lastPostMemberId');

        $owner=$this->getUserService()->findUsersByIds($ownerIds);

        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Group:group-search-result.html.twig',array(
            'groupinfo' => $group,
            'threads'=>$threads,
            'owner'=>$owner,
            'paginator'=>$paginator,
            'lastPostMembers'=>$lastPostMembers,
            'is_groupmember' => $this->getGroupMemberRole($id)));

    }

    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId,'setElite');
    }

    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId,'removeElite');
    }

    public function setStickAction($threadId)
    {
        return $this->postAction($threadId,'setStick');
    }

    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId,'removeStick');
    }

    public function closeThreadAction($threadId,$memberId)
    {
        $thread=$this->getThreadService()->getThread($threadId);

        $groupMemberRole=$this->getGroupMemberRole($thread['groupId']);

        if($groupMemberRole!==2 && $thread['userId']!==$memberId ){

        return new Response($this->generateUrl('group_show', array(
            'id'=>$thread['groupId'],
            )));

        }
        $this->getThreadService()->closeThread($threadId);

        return new Response($this->generateUrl('group_show', array(
            'id'=>$thread['groupId'],
            )));     
    }


    public function deletePostAction($postId,$memberId,$threadOwnerId,$groupOwnerId,$groupId)
    {
        $post=$this->getThreadService()->getPost($postId);

        $groupMemberRole=$this->getGroupMemberRole($groupId);

        if($post['userId']!==$memberId && $memberId!==$threadOwnerId && $groupMemberRole!==2 && $groupMemberRole!==3 && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true){
            
        return new Response($this->generateUrl('group_thread_show', array(
            'id'=>$groupId,'threadId'=>$post['threadId'],
             ))); 
        }
        $this->getThreadService()->deletePost($postId);

        return new Response($this->generateUrl('group_thread_show', array(
            'id'=>$groupId,'threadId'=>$post['threadId'],
            ))); 

    }
    
    private function postAction($threadId,$action)
    {
        $thread=$this->getThreadService()->getThread($threadId);

        $groupMemberRole=$this->getGroupMemberRole($thread['groupId']);

        if($groupMemberRole!==2 && $groupMemberRole!==3 && $this->get('security.context')->isGranted('ROLE_ADMIN')!==true ){

        return new Response($this->generateUrl('group_thread_show', array(
            'id'=>$thread['groupId'],
            'threadId'=>$threadId,
            )));        
        }
        if($action=='setElite'){
           $this->getThreadService()->setElite($threadId); 
        }
        if($action=='removeElite'){
           $this->getThreadService()->removeElite($threadId); 
        }
        if($action=='setStick'){
           $this->getThreadService()->setStick($threadId); 
        }
        if($action=='removeStick'){
           $this->getThreadService()->removeStick($threadId); 
        }

        return new Response($this->generateUrl('group_thread_show', array(
            'id'=>$thread['groupId'],
            'threadId'=>$threadId,
            )));
    }
    private function getPost($postId,$threadId,$id)
    {   
        $post=$this->getThreadService()->getPost($postId);
        if($post['postId']!=0)$postId=$post['postId'];
        $count=$this->getThreadService()->searchPostsCount(array('threadId'=>$threadId,'status'=>'open','id'=>$postId,'postId'=>0));

        $page=floor(($count)/30)+1;
   
        $url=$this->generateUrl('group_thread_show',array('id'=>$id,'threadId'=>$threadId));

        $url=$url."?page=$page#post-$postId";
        return $url;
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

    private function getPostSearchFilters($request)
    {
        $filters=array();

        $filters['type']=$request->query->get('type');

        if(!in_array($filters['type'], array('all','onlyOwner'))){

            $filters['type']='all';
        }

        $filters['sort']=$request->query->get('sort');

        if(!in_array($filters['sort'], array('asc','desc'))){

            $filters['sort']='asc';
        }

        return $filters;
    }
    private function getPostCondition($filters,$ownId,$threadId)
    {
        if($filters=='all') return array('threadId'=>$threadId,'status'=>'open','postId'=>0);

        if($filters=='onlyOwner') return array('threadId'=>$threadId,'status'=>'open','userId'=>$ownId,'postId'=>0);

        return false;

    }
    private function getPostOrderBy($sort)
    {
        if($sort=='asc') return array('createdTime','asc');

        if($sort=='desc') return array('createdTime','desc');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

     private function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byStick':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byCreatedTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byLastPostTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('lastPostTime','DESC'),
                );
                break;
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                );
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }
        return $orderBys;
    }

    private function getGroupMemberRole($userId)
    {
       $user = $this->getCurrentUser();

       if (!$user['id']) return 0;

       if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

       if ($this->getGroupService()->isAdmin($userId, $user['id'])) return 3;

       if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

       return 0;
    }
    

}

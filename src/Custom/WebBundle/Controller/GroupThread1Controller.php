<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\GroupThreadController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class GroupThread1Controller extends GroupThreadController
{
    public function setEliteAction($threadId)
    {   
        $thread=$this->getThreadService()->getThread($threadId);

        if($thread && $thread['isElite'] == 0 )
        $this->getCashService()->reWard(10,"话题被加精",$thread['userId']);

        return $this->postAction($threadId,'setElite');
    }

    public function removeEliteAction($threadId)
    {   
        $thread=$this->getThreadService()->getThread($threadId);

        if($thread && $thread['isElite'] == 1 )
        $this->getCashService()->reWard(10,"话题被取消加精",$thread['userId'],'cut');

        return $this->postAction($threadId,'removeElite');
    }

    private function postAction($threadId,$action)
    {
        $thread=$this->getThreadService()->getThread($threadId);
        $groupMemberRole=$this->getGroupMemberRole($thread['groupId']);

        if($groupMemberRole==2 || $groupMemberRole==3 || $this->get('security.context')->isGranted('ROLE_ADMIN')==true ){
            $threadUrl = $this->generateUrl('group_thread_show', array('id'=>$thread['groupId'],'threadId'=>$thread['id']), true);
            if($action=='setElite'){
               $this->getThreadService()->setElite($threadId);
               $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被设为精华。"); 
            }
            if($action=='removeElite'){
               $this->getThreadService()->removeElite($threadId);
               $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被取消精华。"); 
            }
            if($action=='setStick'){
               $this->getThreadService()->setStick($threadId);
               $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被置顶。"); 
            }
            if($action=='removeStick'){
               $this->getThreadService()->removeStick($threadId);
               $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被取消置顶。"); 
            }

        }
        return new Response($this->generateUrl('group_thread_show', array(
            'id'=>$thread['groupId'],
            'threadId'=>$threadId,
        )));
    }

    public function groupThreadIndexAction(Request $request,$id,$threadId)
    {  
        $group = $this->getGroupService()->getGroup($id);
        if($group['status']=="close"){
            return $this->createMessageResponse('info','该小组已被关闭');
        }

        $user=$this->getCurrentUser();

        $threadMain=$this->getThreadService()->getThread($threadId);

        if (empty($threadMain)) {
            return $this->createMessageResponse('info','该话题已被管理员删除');
        }

        if($threadMain['status']=="close"){
            return $this->createMessageResponse('info','该话题已被关闭');
        }

         if ($threadMain['status']!="close") {
            $isCollected = $this->getThreadService()->isCollected($this->getCurrentUser()->id, $threadMain['id']);
        } else {
            $isCollected = false;
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

        return $this->render('CustomWebBundle:Group:thread.html.twig',array(
            'groupinfo' => $group,
            'isCollected' => $isCollected,
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

    public function rewardAction()
    {   
        
        return $this->render('CustomWebBundle:Group:reward-modal.html.twig',array());
    }

    private function getPostOrderBy($sort)
    {
        if($sort=='asc') return array('createdTime','asc');

        if($sort=='desc') return array('createdTime','desc');
    }

    private function getPostCondition($filters,$ownId,$threadId)
    {
        if($filters=='all') return array('threadId'=>$threadId,'status'=>'open','postId'=>0);

        if($filters=='onlyOwner') return array('threadId'=>$threadId,'status'=>'open','userId'=>$ownId,'postId'=>0);

        return false;

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

    private function getGroupMemberRole($userId)
    {
       $user = $this->getCurrentUser();

       if (!$user['id']) return 0;

       if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

       if ($this->getGroupService()->isAdmin($userId, $user['id'])) return 3;

       if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

       return 0;
    }

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Coin:Cash.CashService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }

    private function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

    public function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}
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
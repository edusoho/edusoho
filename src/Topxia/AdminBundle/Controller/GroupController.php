<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class GroupController extends BaseController
{

	public function IndexAction(Request $request)
    {
		$fields = $request->query->all();

        $conditions = array(
            'status'=>'',
            'title'=>'',
            'ownerName'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        } 

		$paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount($conditions),
            10
        );

		$groupinfo=$this->getGroupService()->searchGroups(
                $conditions,
                array('createdTime','desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        $ownerIds =  ArrayToolkit::column($groupinfo, 'ownerId');
        $owners = $this->getUserService()->findUsersByIds($ownerIds);

		return $this->render('TopxiaAdminBundle:Group:index.html.twig',array(
			'groupinfo'=>$groupinfo,
            'owners'=>$owners,
			'paginator' => $paginator));
	}

    public function threadAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->prepareThreadConditions($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount($conditions),
            10
        );

        $threadinfo=$this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort('byCreatedTime'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $memberIds = ArrayToolkit::column($threadinfo, 'userId');

        $owners = $this->getUserService()->findUsersByIds($memberIds);

        $groupIds =  ArrayToolkit::column($threadinfo, 'groupId');


        $group=$this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('TopxiaAdminBundle:Group:thread.html.twig',array(
            'threadinfo'=>$threadinfo,
            'owners'=>$owners,
            'group'=>$group,
            'paginator' => $paginator));
    }

    public function batchDeleteThreadAction(Request $request)
    {
        $threadIds=$request->request->all();
        foreach ($threadIds['ID'] as $threadId) {
            $this->getThreadService()->deleteThread($threadId); 
        }
        return new Response('success');
    }

    public function threadPostAction()
    {
        return $this->render('TopxiaAdminBundle:Group:threadPost.html.twig',array(
           ));

    }
    public function openGroupAction($id)
    {
        $this->getGroupService()->openGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);

        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }
    public function  closeGroupAction($id)
    {
        $this->getGroupService()->closeGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);
        
        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }

    public function transferGroupAction(Request $request,$groupId)
    {
        $data=$request->request->all();
        $currentUser = $this->getCurrentUser();

        $user=$this->getUserService()->getUserByNickname($data['user']['nickname']);

        $group=$this->getGroupService()->getGroup($groupId);

        $ownerId=$group['ownerId'];

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$ownerId);

        $this->getGroupService()->updateMember($member['id'],array('role'=>'member'));

        if ($currentUser['id'] != $group['ownerId'] ) {

            $this->getNotifiactionService()->notify($group['ownerId'],'default',"您的小组 <a href=\"/group/{$group['id']}\">'{$group['title']}'</a> 被管理员转移给了用户 <a href=\"/user/{$user['id']}\">'{$user['nickname']}'</a>");
            
        }

        $this->getGroupService()->updateGroup($groupId,array('ownerId'=>$user['id']));

        if ($currentUser['id'] != $user['id']) {

            $this->getNotifiactionService()->notify($user['id'],'default',"您获得了小组 <a href=\"/group/{$group['id']}\">'{$group['title']}'</a>的管理权限");
            
        }

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$user['id']);

        if($member){
            $this->getGroupService()->updateMember($member['id'],array('role'=>'owner'));
        }else{
            $this->getGroupService()->addOwner($groupId,$user['id']);
        }

        return new Response("success");
    }

    public function setAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $set=$request->request->all();

            $this->getSettingService()->set('group', $set);
        }

        return $this->render('TopxiaAdminBundle:Group:set.html.twig', array(
        ));
    }
    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId,'removeElite');

    }
    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId,'setElite');

    }
    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId,'removeStick');

    }
    public function setStickAction($threadId)
    {
        return $this->postAction($threadId,'setStick');

    }
    public function closeThreadAction($threadId)
    {
        return $this->postAction($threadId,'closeThread');

    }
    public function openThreadAction($threadId)
    {
        return $this->postAction($threadId,'openThread');

    }
    public function deleteThreadAction($threadId)
    {   
        $thread=$this->getThreadService()->getThread($threadId);
        $threadUrl = $this->generateUrl('group_thread_show', array('id'=>$thread['groupId'],'threadId'=>$thread['id']), true);
        $this->getThreadService()->deleteThread($threadId);
        $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员删除。");
        return $this->createJsonResponse('success');

    }
    private function postAction($threadId,$action)
    {
        $thread=$this->getThreadService()->getThread($threadId);
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
        if($action=='closeThread'){
           $this->getThreadService()->closeThread($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被关闭。");
        }
        if($action=='openThread'){
           $this->getThreadService()->openThread($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被打开。");
        }

        $thread=$this->getThreadService()->getThread($threadId);

        $owners=$this->getUserService()->findUsersByIds(array('0'=>$thread['userId']));

        $group=$this->getGroupService()->getGroupsByIds(array('0'=>$thread['groupId']));


        return $this->render('TopxiaAdminBundle:Group:thread-table-tr.html.twig', array(
            'thread' => $thread,
            'owners'=>$owners,
            'group'=>$group,
        ));

    }

	  protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

     protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function prepareThreadConditions($conditions)
    {

        if (isset($conditions['threadType']) && !empty($conditions['threadType'])) {
            $conditions[$conditions['threadType']] = 1;
            unset($conditions['threadType']);
        }

        if (isset($conditions['groupName']) && $conditions['groupName'] !== "") {
            $group=$this->getGroupService()->findGroupByTitle($conditions['groupName']);
            if (!empty($group)) {
              $conditions['groupId']=$group[0]['id'];  
            } else {
              $conditions['groupId']=0;  
            }
        }
        

        if (isset($conditions['userName']) && $conditions['userName'] !== "") {
            $user=$this->getUserService()->getUserByNickname($conditions['userName']);
            if (!empty($user)) {
              $conditions['userId']=$user['id'];  
            } else {
              $conditions['userId']=0;  
            } 
        }
        
        if (empty($conditions['status'])) {
            unset($conditions['status']);
        }

        return $conditions;
    }

}
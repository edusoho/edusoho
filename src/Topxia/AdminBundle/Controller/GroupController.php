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
        $fields = $request->query->all();

        $conditions = array(
            'status'=>'',
            'title'=>'',
            'groupName'=>'',
            'userName'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        }
        
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

        $user=$this->getUserService()->getUserByNickname($data['user']['nickname']);

        $group=$this->getGroupService()->getGroup($groupId);

        $ownerId=$group['ownerId'];

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$ownerId);

        $this->getGroupService()->updateMember($member['id'],array('role'=>'member'));

        $this->getGroupService()->updateGroup($groupId,array('ownerId'=>$user['id']));

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
        $this->getThreadService()->deleteThread($threadId);

        return $this->createJsonResponse('success');

    }
    private function postAction($threadId,$action)
    {

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
        if($action=='closeThread'){
           $this->getThreadService()->closeThread($threadId); 
        }
        if($action=='openThread'){
           $this->getThreadService()->openThread($threadId); 
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

}
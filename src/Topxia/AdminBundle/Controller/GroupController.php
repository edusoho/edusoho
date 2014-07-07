<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class GroupController extends BaseController
{

	public function IndexAction(Request $request)
    {
		$fields = $request->query->all();
        $conditions = array(
            'enum'=>'',
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

        $ownerIds = array_merge(
            ArrayToolkit::column($groupinfo, 'ownerId')
        );

        $owner=$this->getUserService()->findUsersByIds($ownerIds);

		return $this->render('TopxiaAdminBundle:Group:index.html.twig',array(
			'groupinfo'=>$groupinfo,
            'owner'=>$owner,
			'paginator' => $paginator));
	}
    public function threadAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'enum'=>'',
            'title'=>'',
            'groupName',
        );

        if($request->getMethod()=="POST"){

            $threadIds=$request->request->all();

            if($threadIds['enum']=='open'&&isset($threadIds['ID'])){
                foreach ($threadIds['ID'] as $threadId) {
                $this->postAction($threadId,'openThread');

                }

            }elseif($threadIds['enum']=='close'&&isset($threadIds['ID'])){

                foreach ($threadIds['ID'] as $threadId) {
                
                $this->postAction($threadId,'closeThread');

                }
            }
        }

        if(!empty($fields)){
            $conditions =$fields;
        }
        
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount($conditions),
            10
        );
        $threadinfo=$this->getThreadService()->searchThreads   (
            $conditions,
            $this->filterSort('byCreatedTime'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );

        $memberIds = array_merge(
            ArrayToolkit::column($threadinfo, 'userId')
        );

        $owner=$this->getUserService()->findUsersByIds($memberIds);

        $groupIds = array_merge(
            ArrayToolkit::column($threadinfo, 'groupId')
        );

        $group=$this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('TopxiaAdminBundle:Group:thread.html.twig',array(
            'threadinfo'=>$threadinfo,
            'owner'=>$owner,
            'group'=>$group,
            'paginator' => $paginator));
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

        $owner=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo,
            'owner'=>$owner,
        ));
    }
    public function  closeGroupAction($id)
    {
        $this->getGroupService()->closeGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);

        
        $owner=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Group:table-tr.html.twig', array(
            'group' => $groupinfo,
            'owner'=>$owner,
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

        $owner=$this->getUserService()->findUsersByIds(array('0'=>$thread['userId']));

        $group=$this->getGroupService()->getGroupsByIds(array('0'=>$thread['groupId']));


        return $this->render('TopxiaAdminBundle:Group:thread-table-tr.html.twig', array(
            'thread' => $thread,
            'owner'=>$owner,
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

}
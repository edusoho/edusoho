<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class GroupThreadsController extends BaseController
{
   public function groupThreadAction(Request $request)
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

        return $this->render('TopxiaAdminBundle:Operation:group.thread.html.twig',array(
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


        return $this->render('TopxiaAdminBundle:Operation:thread-table-tr.html.twig', array(
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

}
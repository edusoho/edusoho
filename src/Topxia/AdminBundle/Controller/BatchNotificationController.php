<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class BatchNotificationController extends BaseController
{
	public function indexAction(Request $request){
		$user = $this->getCurrentUser();
    	$conditions = array();
    	$paginator = new Paginator(
            $this->get('request'),
            $this->getBatchNotificationService()->searchBatchNotificationsCount($conditions),
            10
            );
    	$batchnotifications = $this->getBatchNotificationService()->searchBatchNotifications(
    		$conditions, 
    		array('createdTime','DESC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );
    	$userIds =  ArrayToolkit::column($batchnotifications, 'fromId');
        $users = $this->getUserService()->findUsersByIds($userIds);
    	return $this->render('TopxiaAdminBundle:Notification:index.html.twig',array(
    		'paginator' => $paginator,
    		'batchnotifications' => $batchnotifications,
    		'users' => $users
    		));
    }

    public function createAction(Request $request){
    	$user = $this->getCurrentUser();
    	$batchnotification = $request->request->all();
    	if ($request->getMethod() == "POST" ) {
            $batchnotification['fromId'] = $user['id'];
            $batchnotification['content'] = empty($batchnotification['content']) ? '' :$batchnotification['content'] ;
            $batchnotification['title'] = empty($batchnotification['title']) ? '' : $batchnotification['title'];
            if(empty($batchnotification['content']))
            {
            	$this->createMessageResponse('error','群发内容为空');
            }
            if(empty($batchnotification['title']))
            {
                $this->createMessageResponse('error','群发标题为空');
            }
            $batchnotification['createdtime'] = time();
            //（可扩展）默认发送全站私信，可改成群发某个组或者班级成员等
            $batchnotification = $this->getBatchNotificationService()->sendBatchNotification( $batchnotification['fromId'] , $batchnotification['title'] , $batchnotification['content'] , $batchnotification['createdtime'], 'global', 0 ,'text',0);
            return $this->redirect($this->generateUrl('admin_batch_notification'));
        }
        return $this->render('TopxiaAdminBundle:Notification:notification-modal.html.twig',array(
            'batchnotification' => $batchnotification
            ));
    }
    public function editAction(Request $request, $id)
    {
        $batchnotification = $this->getBatchNotificationService()->getBatchNotificationById($id);
        if (empty($batchnotification)) {
            throw $this->createNotFoundException('通知已删除！');
        }
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $batchnotification = $this->getBatchNotificationService()->updateBatchNotification($id, $formData);
            return $this->redirect($this->generateUrl('admin_batch_notification'));
        }
        return $this->render('TopxiaAdminBundle:Notification:notification-modal.html.twig',array(
            'batchnotification' => $batchnotification
        ));
    }
    public function sendAction(Request $request, $id)
    {

        $batchnotification = $this->getBatchNotificationService()->getBatchNotificationById($id);
        if (empty($batchnotification)) {
            throw $this->createNotFoundException('通知已删除！');
        }
        $batchnotification['published'] = $batchnotification['published'] == 0 ? 1 : 0;
        if(!$batchnotification['published'])
        {
            return $this->createJsonResponse(array("status" =>"failed"));
        }
        if ($request->getMethod() == "POST" ) {
            $batchnotification = $this->getBatchNotificationService()->updateBatchNotification($id,$batchnotification);
        }
        return $this->createJsonResponse(array("status" =>"success"));
    }
    public function deleteAction(Request $request,$id)
    {
        if ($request->getMethod() == 'POST') {
            $result = $this->getBatchNotificationService()->deleteBatchNotificationById($id);
            if($result){
                return $this->createJsonResponse(array("status" =>"failed"));
            } else {
                return $this->createJsonResponse(array("status" =>"success")); 
            }
        }
    }

    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }
}
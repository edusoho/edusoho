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
        $users=$this->getUserService()->findUsersByIds($userIds);

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
            if(empty($batchnotification['content']))
            {
            	$this->createMessageResponse('error','群发内容为空');
            }
            $batchnotification['createdtime'] = time();
            //（可扩展）默认发送全站私信，可改成群发某个组或者班级成员等
            $batchnotification = $this->getBatchNotificationService()->sendBatchNotification($batchnotification['fromId'],$batchnotification['title'],$batchnotification['content'],$batchnotification['createdtime'],'global',0,'text');
        }
    	return $this->render('TopxiaAdminBundle:Notification:notification-modal.html.twig',array(
    		'batchnotification' => $batchnotification
    		));
    }
    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }
}
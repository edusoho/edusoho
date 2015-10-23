<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\BatchNotificationService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class BatchNotificationServiceTest extends BaseTestCase
{

	public function testsendBatchNotification()
    {
    	$fromId=1;
    	$title='asmd';
    	$content='sdncsdn';
    	$createdTime = null;
    	$targetType='global';
    	$targetId = 0;
    	$type = "text";
    	$notification=$this->getBatchNotificationService()->sendBatchNotification($fromId, $title,$content,$createdTime,$targetType,$targetId,$type);
    	$this->getBatchNotificationService()->sendBatchNotification($fromId, $title,$content,$createdTime,$targetType,$targetId,$type);
    	//var_dump($notification);
    	$notification1=$this->getBatchNotificationService()->getBatchNotificationById(1);
        $notification2=$this->getBatchNotificationService()->getBatchNotificationById(2);
        var_dump($notification2);
    	//var_dump($notification1);
    	$conditions=array('fromId'=>1);
    	$num=$this->getBatchNotificationService()->searchBatchNotificationsCount($conditions);
    	//var_dump($num);
    	$notifications=$this->getBatchNotificationService()->searchBatchNotifications($conditions,array('createdTime','ASC'),0,9999);
    	//var_dump($notifications);
        $user = $this->createUser();
        $result=$this->getBatchNotificationService()->checkoutBatchNotification($user);
        $this->getBatchNotificationService()->deleteBatchNotificationById(1);
        $notification2=$this->getBatchNotificationService()->getBatchNotificationById(1);
        $notification3=$this->getBatchNotificationService()->getBatchNotificationById(2);
        var_dump($notification2);
        var_dump($notification3);
        $notification3['content'] = empty($notification3['content']) ? 'aaaaaa' :'bbbbbb';
        $this->getBatchNotificationService()->updateBatchNotification(2,$notification3);
        var_dump($notification3);
    }
	protected function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        return $this->getUserService()->register($user);
    }

	protected function getUserService(){
    	return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getBatchNotificationService(){
    	return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }
}
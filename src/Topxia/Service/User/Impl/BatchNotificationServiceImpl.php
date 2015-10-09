<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\BatchNotificationService;

class BatchNotificationServiceImpl extends BaseService implements BatchNotificationService
{
    public function sendBatchNotification($fromId, $title,$content,$createdTime = null,$targetType,$targetId = 0,$type = "text"){
        if (empty($fromId)) {
            throw $this->createServiceException("发件人未注册!");
        }
        if(empty($content)){
            throw $this->createServiceException("抱歉,不能发送空内容!"); 
        }
        $createdTime = empty($createdTime) ? time() : $createdTime;
        $notification = $this->addBatchNotification($type,$title,$fromId, $content,$targetType,$targetId, $createdTime);
        return $notification;
    }

    public function getBatchNotificationById($id){
        return $this->getBatchNotificationDao()->getBatchNotificationById($id);
    }

    public function searchBatchNotificationsCount($conditions){
        return $this->getBatchNotificationDao()->searchBatchNotificationCount($conditions);
    }

    public function searchBatchNotifications($conditions, $orderBy, $start, $limit){
        return $this->getBatchNotificationDao()->searchBatchNotifications($conditions, $orderBy, $start, $limit);
    }
    public function checkoutBatchNotification($user){
        return 0;
    }

    protected function addBatchNotification($type,$title,$fromId,$content,$targetType,$targetId,$createdTime){
        $batchNotification = array(
            'type' => $type,
            'title' =>$title,
            'fromId' => $fromId,
            'content' => $this->purifyHtml($content),
            'targetType' => $targetType,
            'targetId' => $targetId,
            'createdTime' => $createdTime,
        );
        return $this->getBatchNotificationDao()->addBatchNotification($batchNotification);
    }
    protected function getBatchNotificationDao()
    {
        return $this->createDao('User.BatchNotificationDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
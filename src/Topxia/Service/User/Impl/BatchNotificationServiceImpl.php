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
        $conditions = array(
            'userId' => $user['id']
            );
        $notification = $this->getNotificationDao()->searchNotifications($conditions,array('parentId','DESC'),0,1);
        if(!empty($notification) && $notification[0]['parentId'] != 0){
            $conditions = array(
                'id' => $notification[0]['parentId']
                );
        }else{
            $conditions = array(
                'id' => 0
                );
        }
        $batchNotifications = $this->searchBatchNotifications($conditions,array('createdTime','ASC'),0,9999);
        if(!empty($batchNotifications)){
                foreach ($batchNotifications as $key => $batchNotification) {
                    $notification = array(
                        'userId'  => $user['id'],
                        'type'  => $batchNotification['targetType'],
                        'content' => $batchNotification['content'],
                        'parentId'  => $batchNotification['id'],
                        'createdTime'  =>  $batchNotification['createdTime'],
                        );
                    $notification = $this->getNotificationDao()->addNotification(NotificationSerialize::serialize($notification));
                    $this->getUserService()->waveUserCounter($user['id'], 'newNotificationNum', 1);
            }
            return true;
        }
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

    protected function getNotificationDao()
    {
        return $this->createDao('User.NotificationDao');
    }
}
class NotificationSerialize
{
    public static function serialize(array $notification)
    {
        $notification['content'] = json_encode($notification['content']);
        return $notification;
    }
}
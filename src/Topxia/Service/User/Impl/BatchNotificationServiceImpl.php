<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\BatchNotificationService;

class BatchNotificationServiceImpl extends BaseService implements BatchNotificationService
{
    public function sendBatchNotification($fromId, $title,$content,$createdTime = null,$sendedTime,$targetType,$targetId = 0,$type = "text",$published = 0){
        if (empty($fromId)) {
            throw $this->createServiceException("发件人未注册!");
        }
        $createdTime = empty($createdTime) ? time() : $createdTime;
        $sendedTime = $sendedTime  ? time() : $sendedTime;
        $notification = $this->addBatchNotification($type,$title,$fromId, $content,$targetType,$targetId, $createdTime,$sendedTime,$published);
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
            'id' => 0,
            'published' => 1
            );
        $batchNotifications = $this->searchBatchNotifications($conditions,array('createdTime','ASC'),0,9999);
        if(!empty($batchNotifications)){
                foreach ($batchNotifications as $key => $batchNotification) {
                    $conditions = array(
                        'userId' => $user['id'],
                        'type' => 'global',
                        'parentId' => $batchNotification['id']
                    );
                    $notification = $this->getNotificationDao()->searchNotifications($conditions,array('parentId','DESC'),0,1);
                    if(empty($notification)){
                        $content = array(
                            'content' => $batchNotification['content'],
                            'title' => $batchNotification['title']
                            );
                        $notification = array(
                            'userId'  => $user['id'],
                            'type'  => $batchNotification['targetType'],
                            'content' => $content,
                            'parentId'  => $batchNotification['id'],
                            'createdTime'  =>  $batchNotification['sendedTime'],
                            );
                        $notification = $this->getNotificationDao()->addNotification(NotificationSerialize::serialize($notification));
                        $this->getUserService()->waveUserCounter($user['id'], 'newNotificationNum', 1);
                }
            }
            return true;
        }
    }
    public function deleteBatchNotificationById($id)
    {
        $batchNotification = $this->getBatchNotificationDao()->getBatchNotificationById($id);
        if (empty($batchNotification)) {
            throw $this->createServiceException("通知不存在，操作失败。");
        }
        if(!empty($batchNotification)){
            $this->getBatchNotificationDao()->deleteBatchNotification($id);
        }
        return true;
    }

    public function updateBatchNotification($id,$batchNotification)
    {
        if(!empty($batchNotification)){
            $this->getBatchNotificationDao()->updateBatchNotification($id,$batchNotification);
        }
        return true;
    }

    protected function addBatchNotification($type,$title,$fromId,$content,$targetType,$targetId,$createdTime,$sendedTime,$published){
        $batchNotification = array(
            'type' => $type,
            'title' =>$title,
            'fromId' => $fromId,
            'content' => $this->purifyHtml($content),
            'targetType' => $targetType,
            'targetId' => $targetId,
            'createdTime' => $createdTime,
            'sendedTime' => $sendedTime,
            'published' =>$published
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
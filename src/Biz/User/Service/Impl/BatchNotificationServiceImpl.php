<?php
namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\BatchNotificationService;

class BatchNotificationServiceImpl extends BaseService implements BatchNotificationService
{
    public function createBatchNotification($fields)
    {
        if (empty($fields['fromId'])) {
            throw $this->createServiceException('发件人未注册!');
        }
        if (!isset($fields['targetId'])) {
            $fields['targetId'] = 0;
        }
        if (!isset($fields['type'])) {
            $fields['type'] = 'text';
        }
        if (!isset($fields['published'])) {
            $fields['published'] = 0;
        }
        if (!isset($fields['targetType'])) {
            $fields['targetType'] = 'global';
        }
        $fields['createdTime'] = isset($fields['createdTime']) ? time() : $fields['createdTime'];
        $fields['sendedTime']  = 0;
        $notification          = $this->getBatchNotificationDao()->create($fields);
        return $notification;
    }

    public function publishBatchNotification($id)
    {
        $batchNotification = $this->getBatchNotificationDao()->get($id);
        if (empty($batchNotification)) {
            throw $this->createServiceException('不存在此通知!');
        }
        if ($batchNotification['published'] == 1) {
            throw $this->createServiceException('此通知已经发送!');
        }
        $batchNotification['published']  = 1;
        $batchNotification['sendedTime'] = time();
        $this->getBatchNotificationDao()->update($id, $batchNotification);
        return true;
    }

    public function getBatchNotification($id)
    {
        return $this->getBatchNotificationDao()->get($id);
    }

    public function countBatchNotifications($conditions)
    {
        return $this->getBatchNotificationDao()->count($conditions);
    }

    public function searchBatchNotifications($conditions, $orderBy, $start, $limit)
    {
        return $this->getBatchNotificationDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function checkoutBatchNotification($userId)
    {
        $conditions = array(
            'userId' => $userId,
            'type'   => 'global'
        );
        $notification = $this->getNotificationDao()->search($conditions, array('createdTime' => 'DESC'), 0, 1);
        $comparetime  = $notification ? $notification[0]['createdTime'] : 0;
        $conditions   = array(
            'id'          => 0,
            'published'   => 1,
            'createdTime' => $comparetime
        );
        $batchNotifications = $this->searchBatchNotifications($conditions, array('createdTime' => 'DESC'), 0, 10);
        $user               = $this->getUserService()->getUser($userId);
        foreach ($batchNotifications as $key => $batchNotification) {
            if ($batchNotification['sendedTime'] > $user['createdTime']) {
                $content = array(
                    'content' => $batchNotification['content'],
                    'title'   => $batchNotification['title']
                );
                $notification = array(
                    'userId'      => $userId,
                    'type'        => $batchNotification['targetType'],
                    'content'     => $content,
                    'batchId'     => $batchNotification['id'],
                    'createdTime' => $batchNotification['sendedTime']
                );
                $this->getNotificationDao()->create($notification);
                $this->getUserService()->waveUserCounter($userId, 'newNotificationNum', 1);
            }
        }
    }

    public function deleteBatchNotification($id)
    {
        $batchNotification = $this->getBatchNotificationDao()->get($id);
        if (empty($batchNotification)) {
            throw $this->createServiceException('通知不存在，操作失败。');
        }
        if (!empty($batchNotification)) {
            $this->getBatchNotificationDao()->delete($id);
        }
        return true;
    }

    public function updateBatchNotification($id, $fields)
    {
        if (!empty($fields)) {
            $this->getBatchNotificationDao()->update($id, $fields);
        }
        return true;
    }

    protected function addBatchNotification($type, $title, $fromId, $content, $targetType, $targetId, $createdTime, $sendedTime, $published)
    {
        $batchNotification = array(
            'type'        => $type,
            'title'       => $title,
            'fromId'      => $fromId,
            'content'     => $this->purifyHtml($content),
            'targetType'  => $targetType,
            'targetId'    => $targetId,
            'createdTime' => $createdTime,
            'sendedTime'  => $sendedTime,
            'published'   => $published
        );
        return $this->getBatchNotificationDao()->create($batchNotification);
    }

    protected function getBatchNotificationDao()
    {
        return $this->createDao('User:BatchNotificationDao');
    }

    protected function getNotificationDao()
    {
        return $this->createDao('User:NotificationDao');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}

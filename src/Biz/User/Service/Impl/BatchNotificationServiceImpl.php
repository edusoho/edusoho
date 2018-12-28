<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Notification\NotificationException;
use Biz\User\Service\BatchNotificationService;

class BatchNotificationServiceImpl extends BaseService implements BatchNotificationService
{
    public function createBatchNotification($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'content'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $mode = empty($fields['mode']) ? '' : $fields['mode'];
        unset($fields['mode']);

        $user = $this->getCurrentUser();
        $fields['fromId'] = $user['id'];

        $fields['title'] = $this->purifyHtml($fields['title']);
        $fields['targetId'] = empty($fields['targetId']) ? 0 : $fields['targetId'];
        $fields['type'] = empty($fields['type']) ? 'text' : $fields['type'];
        $fields['published'] = empty($fields['published']) ? 0 : $fields['published'];
        $fields['targetType'] = empty($fields['targetType']) ? 'global' : $fields['targetType'];
        $fields['createdTime'] = empty($fields['createdTime']) ? time() : $fields['createdTime'];
        $fields['sendedTime'] = 0;
        $notification = $this->getBatchNotificationDao()->create($fields);

        if (!empty($mode) && $mode == 'publish') {
            $this->publishBatchNotification($notification['id']);
        }

        return $notification;
    }

    public function publishBatchNotification($id)
    {
        $batchNotification = $this->getBatchNotificationDao()->get($id);
        if (empty($batchNotification)) {
            $this->createNewException(NotificationException::BATCH_NOTIFICATION_NOT_FOUND());
        }
        if ($batchNotification['published'] == 1) {
            $this->createNewException(NotificationException::PUBLISHED_BATCH_NOTIFICATION());
        }
        $batchNotification['published'] = 1;
        $batchNotification['sendedTime'] = time();
        $batchNotification = $this->getBatchNotificationDao()->update($id, $batchNotification);

        $this->dispatchEvent('batch_notification.publish', $batchNotification);

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
        $user = $this->getUserService()->getUser($userId);
        $batchIds = $this->getNotificationService()->findBatchIdsByUserIdAndType($userId, 'global');
        $batchIds = ArrayToolkit::column($batchIds, 'batchId');

        $batchNotifications = $this->searchBatchNotifications(
            array(
                'excludeIds' => $batchIds,
                'type' => 'global',
                'published' => 1,
                'sendedTime_LE' => time(),
                'sendedTime_GE' => $user['createdTime'],
            ),
            array(),
            0,
            PHP_INT_MAX);
        foreach ($batchNotifications as $key => $batchNotification) {
            $content = array(
                'content' => $batchNotification['content'],
                'title' => $batchNotification['title'],
            );
            $notification = array(
                'userId' => $userId,
                'type' => $batchNotification['targetType'],
                'content' => $content,
                'batchId' => $batchNotification['id'],
                'createdTime' => $batchNotification['sendedTime'],
            );
            $this->getNotificationDao()->create($notification);
            $this->getUserService()->waveUserCounter($userId, 'newNotificationNum', 1);
        }
    }

    public function deleteBatchNotification($id)
    {
        $batchNotification = $this->getBatchNotificationDao()->get($id);
        if (!$batchNotification) {
            $this->createNewException(NotificationException::BATCH_NOTIFICATION_NOT_FOUND());
        }

        $this->getBatchNotificationDao()->delete($id);

        return true;
    }

    public function updateBatchNotification($id, $fields)
    {
        if (empty($fields)) {
            return array();
        }

        $mode = empty($fields['mode']) ? '' : $fields['mode'];
        unset($fields['mode']);

        $notification = $this->getBatchNotificationDao()->update($id, $fields);

        if (!empty($mode) && $mode == 'publish') {
            $this->publishBatchNotification($notification['id']);
        }

        return $notification;
    }

    protected function addBatchNotification($type, $title, $fromId, $content, $targetType, $targetId, $createdTime, $sendedTime, $published)
    {
        $batchNotification = array(
            'type' => $type,
            'title' => $title,
            'fromId' => $fromId,
            'content' => $this->purifyHtml($content),
            'targetType' => $targetType,
            'targetId' => $targetId,
            'createdTime' => $createdTime,
            'sendedTime' => $sendedTime,
            'published' => $published,
        );

        return $this->getBatchNotificationDao()->create($batchNotification);
    }

    protected function getBatchNotificationDao()
    {
        return $this->createDao('User:BatchNotificationDao');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
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

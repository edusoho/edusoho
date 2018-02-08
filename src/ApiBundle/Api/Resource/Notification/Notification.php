<?php

namespace ApiBundle\Api\Resource\Notification;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class Notification extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $startTime = $request->query->get('startTime', 0);
        $type = $request->query->get('type', 'course');

        $conditions = array(
            'createdTime_GT' => $startTime,
            'userId' => $user['id'],
        );
        $conditions = $this->filterType($type, $conditions);

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $notifications = $this->getNotificationService()->searchNotifications(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->getNotificationService()->countNotifications($conditions);

        $notifications = $this->filterUserFollow($notifications);

        return $this->makePagingObject($notifications, $total, $offset, $limit);
    }

    protected function filterType($type, $conditions)
    {
        $typeMap = array(
            'friend' => array('user-follow'),
            'course' => array(
                'cash_account',
                'classroom-deadline',
                'classroom-student',
                'coupon',
                'course-deadline',
                'course-student',
                'course-thread',
                'discount',
                'invite-reward',
                'learn-notice',
                'live-course',
                'order_refund',
                'questionRemind',
                'role',
                'materialLib',
                'student-create',
                'student-remove',
                'test-paper',
                'thread',
                'vip',
                'vip-deadline',
            ),
        );

        if (!empty($typeMap[$type])) {
            $conditions['types'] = $typeMap[$type];
        }

        return $conditions;
    }

    private function filterUserFollow($notifications)
    {
        $userIds = array_map(function ($notification) {
            if ($notification['type'] == 'user-follow') {
                return $notification['content']['userId'];
            }
        }, $notifications);

        if (empty($userIds)) {
            return $notifications;
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($notifications as $key => &$notification) {
            if ($notification['type'] != 'user-follow') {
                continue;
            }

            $isUnfollow = !empty($notification['content']['opration']) && $notification['content']['opration'] == 'unfollow';

            if ($isUnfollow) {
                unset($notifications[$key]);
                continue;
            }

            $userId = $notification['content']['userId'];
            $notification['content']['followUser'] = empty($users[$userId]) ? null : $users[$userId];
        }

        return array_values($notifications);
    }

    protected function getNotificationService()
    {
        return $this->service('User:NotificationService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}

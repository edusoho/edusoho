<?php

namespace ApiBundle\Api\Resource\Notification;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Notification extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $startTime = $request->query->get('startTime', time());
        $type = $request->query->get('type', 'course');

        $conditions = array(
            'createdTime_GT' => $startTime,
            'userId' => $user['id'],
        );
        $conditions = $this->filterType($type, $conditions);

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->getNotificationService()->searchNotifications(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->getNotificationService()->countNotifications($conditions);

        return $this->makePagingObject($courses, $total, $offset, $limit);
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
                'notification.learn-notice',
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
                'vip-deadline'
            ),
        );

        if (!empty($typeMap[$type])) {
            $conditions['types'] = $typeMap[$type];
        }

        return $conditions;
    }

    protected function getNotificationService()
    {
        return $this->service('User:NotificationService');
    }
}
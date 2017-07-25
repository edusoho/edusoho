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

        $requestData = $request->query->all();
        $conditions = array(
            'createdTime_GT' => $requestData['startTime'],
            'userId' => $user['id'],
            'typeExclude' => array('group-profile','group-thread','comment-post','truename-authenticate')
        );

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

    protected function getNotificationService()
    {
        return $this->service('User:NotificationService');
    }
}
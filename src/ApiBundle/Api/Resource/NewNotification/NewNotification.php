<?php

namespace ApiBundle\Api\Resource\NewNotification;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class NewNotification extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $newNotificationNum = $user['newNotificationNum'];
        if ($newNotificationNum > 5) {
            $newNotificationNum = 5;
        }
        $newNotifications = $this->getNotificationService()->searchNotifications(
            array('userId' => $user->id, 'isRead' => 0),
            array('createdTime' => 'DESC'),
            0,
            $newNotificationNum
        );

        return $this->renderView('ApiBundle:newNotification:user-inform-notification.html.twig', array(
            'notifications' => $newNotifications,
        ));
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

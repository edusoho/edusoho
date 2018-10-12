<?php
/**
 * This file is part of the edusoho.
 * User: Ilham Tahir <yantaq@bilig.biz>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ApiBundle\Api\Resource\BatchNotification;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\BatchNotificationService;
use Biz\Notification\NotificationException;

class BatchNotification extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $batchNotification = $this->getBatchNotificationService()->getBatchNotification($id);
        if (!$batchNotification) {
            throw NotificationException::BATCH_NOTIFICATION_NOT_FOUND();
        }

        return $batchNotification;
    }

    /**
     * @return BatchNotificationService
     */
    protected function getBatchNotificationService()
    {
        return $this->service('User:BatchNotificationService');
    }
}

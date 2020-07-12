<?php

namespace Biz\S2B2C\Service;

use ApiBundle\Api\Resource\SyncProductNotify\NotifyEvent;

interface SupplierProductNotifyService
{
    public function refreshProductsStatus($params);

    /**
     * @param NotifyEvent $notifyEvent
     *
     * @return mixed
     */
    public function syncSupplierProductEvent($notifyEvent);
}

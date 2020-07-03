<?php

namespace Biz\S2B2C\Service;

use ApiBundle\Api\Resource\SyncProductNotify\NotifyEvent;

interface SupplierProductNotifyService
{
    public function setProductHasNewVersion($params);

    public function refreshProductsStatus($params);

    public function supplierCourseClosed($params);

    public function supplierCourseSetClosed($params);

    /**
     * @param NotifyEvent $notifyEvent
     * @return mixed
     */
    public function syncSupplierProductEvent($notifyEvent);
}

<?php

namespace Biz\S2B2C\Service;

interface SupplierProductNotifyService
{
    public function setProductHasNewVersion($params);

    public function refreshProductsStatus($params);

    public function supplierCourseClosed($params);

    public function supplierCourseSetClosed($params);
}

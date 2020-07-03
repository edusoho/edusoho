<?php

namespace Biz\S2B2C\Service;

interface SupplierNotifyService
{
    public function onSiteStatusChange($params);

    public function onCoopModeChange($params);

    public function onMerchantDomainUrlChange($params);

    public function onSupplierDomainUrlChange($params);

    public function onSupplierSiteLogoAndFaviconChange($params);

    public function onMerchantAuthNodeChange($params);

    public function onResetMerchantBrand($params);
}

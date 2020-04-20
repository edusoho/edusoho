<?php

namespace Biz\S2B2C\Service;

interface S2B2CFacadeService
{
    const DEALER_MODE = 'dealer'; // 经销模式
    const FRANCHISEE_MODE = 'franchisee'; // 加盟模式

    public function getMe();

    public function getSupplier();

    public function getMerchantDisabledPermissionList();

    public function getSupplierPlatformApi();

    public function getS2B2CService();
}

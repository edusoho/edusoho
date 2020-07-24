<?php

namespace Biz\S2B2C\Service;

use Biz\S2B2C\SupplierPlatformApi;
use QiQiuYun\SDK\Service\S2B2CService;

interface S2B2CFacadeService
{
    const DEALER_MODE = 'dealer'; // 经销模式
    const FRANCHISEE_MODE = 'franchisee'; // 加盟模式

    public function getMe();

    public function getSupplier();

    public function getS2B2CConfig();

    public function getBehaviourPermissions();

    public function getMerchantDisabledPermissions();

    public function updateMerchantDisabledPermissions();

    /**
     * @return SupplierPlatformApi
     */
    public function getSupplierPlatformApi();

    /**
     * @return S2B2CService
     */
    public function getS2B2CService();
}

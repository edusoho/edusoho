<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;
use Biz\System\Service\CacheService;

class S2B2CFacadeServiceImpl extends BaseService implements S2B2CFacadeService
{
    /**
     * @return array|string[]
     *                        cached
     */
    public function getMe()
    {
        if ($this->isS2B2CInfoValid('me')) {
            return $this->biz['s2b2c_info']['me']['data'];
        }

        $me = $this->getS2B2CService()->getMe();
        if (!isset($me['error'])) {
            $this->cacheS2B2CInfo('me', $me);
        }

        return $me;
    }

    /**
     * @return array|string[]
     *                        cached
     */
    public function getSupplier()
    {
        if ($this->isS2B2CInfoValid('supplier')) {
            return $this->biz['s2b2c_info']['supplier']['data'];
        }

        $supplier = $this->getS2B2CService()->getOwnSupplier();
        if (!isset($supplier['error'])) {
            $this->cacheS2B2CInfo('supplier', $supplier);
        }

        return $supplier;
    }

    public function getMerchantDisabledPermissions()
    {
        $disabledList = $this->getCacheService()->get('s2b2c_disabled_permission_list');
        if (empty($disabledList)) {
            $disabledList = $this->getSupplierPlatformApi()->getMerchantDisabledPermissions();
            $this->getCacheService()->set('s2b2c_disabled_permission_list', $disabledList, time() + 86400);

            return $disabledList;
        }

        return $disabledList;
    }

    /**
     * @return SupplierPlatformApi
     *                             具体接口太片面，应该抽象成商品，而不是具体的课程，暂不封装具体接口，这里只做衔接
     */
    public function getSupplierPlatformApi()
    {
        return $this->biz->offsetGet('supplier.platform_api');
    }

    /**
     * @return \QiQiuYun\SDK\Service\S2B2CService
     *                                            除了需要缓存的接口，其他的通过getS2B2CService调用
     */
    public function getS2B2CService()
    {
        return $this->biz->offsetGet('qiQiuYunSdk.s2b2cService');
    }

    /**
     * @param $module
     *
     * @return bool
     */
    private function isS2B2CInfoValid($module)
    {
        if (isset($this->biz['s2b2c_info'][$module]) && time() < $this->biz['s2b2c_info'][$module]['deadline']) {
            return true;
        }

        return false;
    }

    /**
     * @param $module
     * @param $info
     */
    private function cacheS2B2CInfo($module, $info)
    {
        $s2b2cInfo = [
            $module => [
                'data' => $info,
                'deadline' => time() + 60,
            ],
        ];
        if ($this->biz->offsetExists('s2b2c_info')) {
            $s2b2cInfo = array_merge($this->biz->offsetget('s2b2c_info'), $s2b2cInfo);
        }

        $this->biz->offsetSet('s2b2c_info', $s2b2cInfo);
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}

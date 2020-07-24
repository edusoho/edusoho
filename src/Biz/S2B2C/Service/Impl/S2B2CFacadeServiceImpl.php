<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\S2B2C\S2B2CException;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;
use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;
use QiQiuYun\SDK\Service\S2B2CService;

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

    /**
     * @return array []
     *               [
     *               'enabled' => $biz['s2b2c.options']['enabled'],
     *               'supplierId' => $biz['s2b2c.options']['supplierId'],
     *               'supplierDomain' => $biz['s2b2c.options']['supplierDomain'],
     *               'businessMode' => $biz['s2b2c.options']['businessMode'],
     *               ]
     */
    public function getS2B2CConfig()
    {
        return $this->biz['s2b2c.config'];
    }

    public function getBehaviourPermissions()
    {
        if ($this->isS2B2CInfoValid('behaviour_permissions')) {
            return $this->biz['s2b2c_info']['behaviour_permissions']['data'];
        }

        $s2b2cConfig = $this->biz['s2b2c.config'];
        $s2b2cSetting = $this->getSettingService()->get('s2b2c', []);
        $authNode = empty($s2b2cSetting['auth_node']) ? [] : $s2b2cSetting['auth_node'];
        $behaviourPermissions = [
            'canModifySiteName' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]) || !empty($authNode['title']),
            'canModifySiteUrl' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]),
            'canModifySiteLogo' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]) || !empty($authNode['logo']),
            'canModifySiteFavicon' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]) || !empty($authNode['favicon']),
            'canModifyCoursePrice' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]),
            'canAddCourse' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]),
            'canAddLiveCourse' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]),
            'canAddOpenCourse' => !$s2b2cConfig['enabled'] || in_array($s2b2cConfig['businessMode'], [self::DEALER_MODE]),
            'canManageCourseSetFiles' => !$s2b2cConfig['enabled'], //文件的读写收到多方面因素的影响，原逻辑并不完善
        ];
        $this->cacheS2B2CInfo('behaviour_permissions', $behaviourPermissions);

        return $behaviourPermissions;
    }

    public function getMerchantDisabledPermissions()
    {
        $disabledPermissions = $this->getCacheService()->get('s2b2c_disabled_permissions');
        if (empty($disabledPermissions)) {
            $disabledPermissions = $this->updateMerchantDisabledPermissions();
        }

        return $disabledPermissions;
    }

    public function updateMerchantDisabledPermissions()
    {
        $disabledPermissions = $this->getSupplierPlatformApi()->getMerchantDisabledPermissions();
        if (empty($disabledPermissions) || !empty($disabledPermissions['error'])) {
            // throw S2B2CException::INVALID_S2B2C_HIDDEN_PERMISSION();
        }
        $this->getCacheService()->set('s2b2c_disabled_permissions', $disabledPermissions, time() + 86400);

        return $disabledPermissions;
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
     * @return S2B2CService
     *                      除了需要缓存的接口，其他的通过getS2B2CService调用
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
        $s2b2cInfo = $this->biz->offsetExists('s2b2c_info') ? $this->biz->offsetGet('s2b2c_info') : [];
        if (isset($s2b2cInfo[$module]) && time() < $s2b2cInfo[$module]['deadline']) {
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

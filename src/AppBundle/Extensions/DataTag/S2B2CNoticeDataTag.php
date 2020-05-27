<?php

namespace AppBundle\Extensions\DataTag;

use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;

class S2B2CNoticeDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        $merchant = $this->getS2B2CFacadeService()->getMe();
        $supplier = $this->getS2B2CFacadeService()->getSupplier();

        $hasNewVersion = $this->getCacheService()->get('s2b2c.hasNewVersion');
        $balance = empty($merchant['balance']) ? 0 : $merchant['balance'];

        $noticeNum = 0;
        $contents = [
            'balance' => $balance,
            'notEnoughBalance' => (isset($merchant['balance']) && $balance < 500000),
            'hasNewVersionProducts' => empty($hasNewVersion['courseSet']) ? false : true,
            'supplierName' => empty($supplier['name']) ? '-' : $supplier['name'],
        ];
        if ($contents['notEnoughBalance']) {
            ++$noticeNum;
        }
        if ($contents['hasNewVersionProducts']) {
            ++$noticeNum;
        }
        $contents['noticeNum'] = $noticeNum;

        return $contents;
    }

    /**
     * @return \Biz\S2B2C\Service\ProductService
     */
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}

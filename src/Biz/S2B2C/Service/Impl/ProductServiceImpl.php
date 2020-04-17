<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Service\BaseService;

class ProductServiceImpl extends BaseService implements ProductService
{
    public function searchProduct($conditions)
    {
        $selectedConditions = array('title', 'offset', 'limit', 'categoryId', 'sort');
        $conditions = ArrayToolkit::parts($conditions, $selectedConditions);
        $conditions['merchant_access_key'] = $this->getAccessKey();
        if (isset($conditions['title']) && empty($conditions['title'])) {
            unset($conditions['title']);
        }

        $courseSets = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->searchSupplierProducts($conditions);

        if (!empty($courseSets['error'])) {
            $total = 0;
            $courseSets = array();
        } else {
            $total = $courseSets['paging']['total'];
            $courseSets = $courseSets['data'];
        }

        return array($courseSets, $total);
    }

    public function searchSelectedItemProduct($conditions)
    {
        $conditions['merchant_access_key'] = $this->getAccessKey();
        $resultSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->searchPurchaseProducts($conditions);

        return $resultSet;
    }

    protected function getAccessKey()
    {
        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            throw new \RuntimeException('系统尚未配置AccessKey/SecretKey');
        }

        return $settings['cloud_access_key'];
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }
}

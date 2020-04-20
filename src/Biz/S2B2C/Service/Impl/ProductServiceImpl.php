<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\S2B2C\Dao\ProductDao;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;

/**
 * Class ProductServiceImpl
 * @package Biz\S2B2C\Service\Impl
 * 这里书写抽象的Product业务逻辑代码
 */
class ProductServiceImpl extends BaseService implements ProductService
{
    public function getProduct($id)
    {
        return $this->getS2B2CProductDao()->get($id);
    }

    public function createProduct($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('supplierId', 'resourceType', 'remoteResourceId', 'localResourceId', 'cooperationPrice', 'suggestionPrice', 'localVersion'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'supplierId',
                'resourceType',
                'remoteResourceId',
                'localResourceId',
                'cooperationPrice',
                'suggestionPrice',
                'localVersion',
                'createdTime',
                'updatedTime'
            )
        );
        return $this->getS2B2CProductDao()->create($fields);
    }

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

    /**
     * @return ProductDao
     */
    protected function getS2B2CProductDao()
    {
        return $this->biz->dao('S2B2C:ProductDao');
    }
}

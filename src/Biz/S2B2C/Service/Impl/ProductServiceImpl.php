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
 */
class ProductServiceImpl extends BaseService implements ProductService
{
    public function getProduct($id)
    {
        return $this->getS2B2CProductDao()->get($id);
    }

    public function getProductBySupplierIdAndRemoteProductId($supplierId, $remoteProductId)
    {
        return $this->getS2B2CProductDao()->getBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);
    }

    public function findProductsBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds)
    {
        if (empty($remoteProductIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);
    }

    public function findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds)
    {
        if (empty($remoteResourceIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds);
    }

    public function createProduct($fields)
    {
        if (!ArrayToolkit::requireds(
            $fields,
            ['supplierId', 'productType', 'remoteProductId', 'remoteResourceId', 'localResourceId']
        )) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts(
            $fields,
            [
                'supplierId',
                'productType',
                'remoteProductId',
                'remoteResourceId',
                'localResourceId',
                'cooperationPrice',
                'suggestionPrice',
                'localVersion',
                'createdTime',
                'updatedTime',
            ]
        );

        return $this->getS2B2CProductDao()->create($fields);
    }

    public function searchProduct($conditions)
    {
        $selectedConditions = ['title', 'offset', 'limit', 'categoryId', 'sort'];
        $conditions = ArrayToolkit::parts($conditions, $selectedConditions);
        $conditions['merchant_access_key'] = $this->getAccessKey();
        if (isset($conditions['title']) && empty($conditions['title'])) {
            unset($conditions['title']);
        }

        $courseSets = $this->getS2B2CFacadeService()->getSupplierPlatformApi()
            ->searchSupplierProducts($conditions);

        if (!empty($courseSets['error'])) {
            $total = 0;
            $courseSets = [];
        } else {
            $total = $courseSets['paging']['total'];
            $courseSets = $courseSets['data'];
        }

        return [$courseSets, $total];
    }

    public function searchSelectedItemProduct($conditions)
    {
        $conditions['merchant_access_key'] = $this->getAccessKey();
        $resultSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->searchPurchaseProducts($conditions);

        return $resultSet;
    }

    protected function getAccessKey()
    {
        $settings = $this->getSettingService()->get('storage', []);
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

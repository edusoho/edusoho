<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Dao\ProductDao;
use Biz\S2B2C\S2B2CProductException;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

/**
 * Class ProductServiceImpl
 */
class ProductServiceImpl extends BaseService implements ProductService
{
    public function getProduct($id)
    {
        return $this->getS2B2CProductDao()->get($id);
    }

    /**
     * @param $supplierId
     * @param $remoteProductId
     *
     * @return mixed
     *               通过supplierId 和 remoteProductId（第三方商品ID）唯一确定一个商品
     */
    public function getProductBySupplierIdAndRemoteProductId($supplierId, $remoteProductId)
    {
        return $this->getS2B2CProductDao()->getBySupplierIdAndRemoteProductId($supplierId, $remoteProductId);
    }

    /**
     * @param $supplierId
     * @param $remoteProductId
     * @param $type
     *
     * @return mixed
     *               通过supplierId 和 remoteProductId（第三方商品ID）和 type 唯一确定一个商品
     */
    public function getProductBySupplierIdAndRemoteProductIdAndType($supplierId, $remoteProductId, $type)
    {
        return $this->getS2B2CProductDao()->getBySupplierIdAndRemoteProductIdAndType($supplierId, $remoteProductId, $type);
    }

    /**
     * @param $supplierId
     * @param $remoteResourceId
     * @param $type
     *
     * @return mixed
     *               通过supplierId 和 remoteResourceId（第三方具体资源ID）和 类型 唯一确定一个商品
     */
    public function getProductBySupplierIdAndRemoteResourceIdAndType($supplierId, $remoteResourceId, $type)
    {
        return $this->getS2B2CProductDao()->getBySupplierIdAndRemoteResourceIdAndType($supplierId, $remoteResourceId, $type);
    }

    /**
     * @param $s2b2cProductId
     * @param $remoteResourceId
     * @param $type
     *
     * @return mixed
     *               通过$s2b2cProductId 和 remoteResourceId（第三方具体资源ID）和 类型 唯一确定一个商品
     */
    public function getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $remoteResourceId, $type)
    {
        return $this->getS2B2CProductDao()->getByRemoteProductIdRemoteResourceIdAndType($s2b2cProductId, $remoteResourceId, $type);
    }

    /**
     * @param $supplierId
     * @param $localResourceId
     * @param $type
     *
     * @return mixed
     *               通过supplierId 和 localResourceId（本地具体资源ID）和 类型 唯一确定一个商品
     */
    public function getProductBySupplierIdAndLocalResourceIdAndType($supplierId, $localResourceId, $type)
    {
        return $this->getS2B2CProductDao()->getBySupplierIdAndLocalResourceIdAndType($supplierId, $localResourceId, $type);
    }

    /**
     * @param $supplierId
     * @param $productType
     *
     * @return mixed
     *               通过supplierId和productType唯一确定一批products
     */
    public function findProductsBySupplierIdAndProductType($supplierId, $productType)
    {
        return $this->getS2B2CProductDao()->findBySupplierIdAndProductType($supplierId, $productType);
    }

    /**
     * @param $supplierId
     * @param $remoteProductIds
     *
     * @return array
     *               通过supplierId 和 remoteProductIds（第三方商品IDS）唯一确定一批商品
     */
    public function findProductsBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds)
    {
        if (empty($remoteProductIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndRemoteProductIds($supplierId, $remoteProductIds);
    }

    /**
     * @param $supplierId
     * @param $productType
     * @param $remoteResourceIds
     *
     * @return array
     *               通过supplierId 和 remoteResourceIds (远程的具体商品的ID) 和 商品类型 productType唯一确定一批商品
     */
    public function findProductsBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds)
    {
        if (empty($remoteResourceIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndRemoteResourceTypeAndIds($supplierId, $productType, $remoteResourceIds);
    }

    public function findProductsBySupplierIdAndRemoteResourceTypeAndProductIds($supplierId, $productType, $remoteProductIds)
    {
        if (empty($remoteProductIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndRemoteResourceTypeAndProductIds($supplierId, $productType, $remoteProductIds);
    }

    /**
     * @param $supplierId
     * @param $productType
     * @param $localResourceIds
     *
     * @return array
     *               通过supplierId 和 localResourceIds (本地的具体商品的ID) 和 商品类型 productType唯一确定一批商品
     */
    public function findProductsBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds)
    {
        if (empty($localResourceIds)) {
            return [];
        }

        return $this->getS2B2CProductDao()->findBySupplierIdAndProductTypeAndLocalResourceIds($supplierId, $productType, $localResourceIds);
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
                'remoteVersion',
                'createdTime',
                'updatedTime',
                'syncStatus',
                's2b2cProductDetailId',
            ]
        );

        return $this->getS2B2CProductDao()->create($fields);
    }

    public function updateProduct($id, $productFields)
    {
        $productFields = ArrayToolkit::parts(
            $productFields,
            [
                'supplierId',
                'productType',
                'remoteProductId',
                's2b2cProductDetailId',
                'remoteResourceId',
                'localResourceId',
                'cooperationPrice',
                'suggestionPrice',
                'localVersion',
                'remoteVersion',
                'createdTime',
                'updatedTime',
                'syncStatus',
                'changelog',
            ]
        );

        return $this->getS2B2CProductDao()->update($id, $productFields);
    }

    public function deleteProduct($id)
    {
        return $this->getS2B2CProductDao()->delete($id);
    }

    /**
     * @param $nowVersion
     * @param $productVersions
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function generateVersionChangeLogs($nowVersion, $productVersions)
    {
        if (empty($productVersions)) {
            return [];
        }
        $changeLogs = [];
        foreach ($productVersions as $productVersion) {
            if ($productVersion['productVersion'] <= $nowVersion) {
                continue;
            }
            $changeLogs[$productVersion['productVersion']] = $productVersion['changeDetail'];
        }

        return $changeLogs;
    }

    public function searchRemoteProducts($conditions)
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

    public function searchProducts($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getS2B2CProductDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countProducts($conditions)
    {
        return $this->getS2B2CProductDao()->count($conditions);
    }

    public function searchSelectedProducts($conditions)
    {
        $conditions['merchant_access_key'] = $this->getAccessKey();

        return $this->getS2B2CFacadeService()->getSupplierPlatformApi()->searchPurchaseProducts($conditions);
    }

    public function getByTypeAndLocalResourceId($type, $localResourceId)
    {
        return $this->getS2B2CProductDao()->getByTypeAndLocalResourceId($type, $localResourceId);
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getProductUpdateType()
    {
        return $this->getSettingService()->get('productUpdateType');
    }

    public function setProductUpdateType($type)
    {
        if (!in_array($type, [self::UPDATE_TYPE_AUTO, self::UPDATE_TYPE_MANUAL])) {
            $this->createNewException(S2B2CProductException::INVALID_S2B2C_PRODUCT_TYPE());
        }

        if (self::UPDATE_TYPE_AUTO == $type) {
            $this->addUpdateProductJob();
        } else {
            $this->removeUpdateProductJob();
        }

        return $this->getSettingService()->set('productUpdateType', $type);
    }

    protected function addUpdateProductJob()
    {
        $job = $this->getSchedulerService()->getJobByName('productUpdateVersion');

        if (!empty($job)) {
            return true;
        }

        $job = [
            'name' => 'productUpdateVersion',
            'class' => 'Biz\S2B2C\Job\UpdateProductVersionJob',
            'expression' => '0 2 * * *',
            'args' => '',
            'misfire_threshold' => 300,
            'misfire_policy' => 'executing',
        ];

        $this->getSchedulerService()->register($job);

        return true;
    }

    protected function removeUpdateProductJob()
    {
        $job = $this->getSchedulerService()->getJobByName('productUpdateVersion');

        if (empty($job)) {
            return true;
        }

        $this->getSchedulerService()->deleteJob($job['id']);

        return true;
    }

    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return true;
        }

        return $this->getS2B2CProductDao()->deleteByIds($ids);
    }

    /**
     * @param $s2b2cProductId
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function adoptProduct($s2b2cProductId)
    {
        $supplier = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $localProduct = $this->getProductBySupplierIdAndRemoteProductId($supplier['supplierId'], $s2b2cProductId);

        if (!empty($localProduct)) {
            $this->createNewException(S2B2CProductException::ADOPT_PRODUCT_REPEAT());
        }

        $this->adoptS2B2CProduct($s2b2cProductId);

        $this->beginTransaction();
        try {
            //@todo 可以改成策略 根据商品类型进行同步，暂时只有course_set类型
            $this->getCourseProductService()->syncCourses($s2b2cProductId);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            $this->biz->offsetGet('s2b2c.merchant.logger')->error('[adoptProduct] 同步课程失败，原因:'.$exception->getMessage());
            $this->createNewException(S2B2CProductException::SYNC_PRODUCT_CONTENT_FAIL());
        }

        return true;
    }

    protected function adoptS2B2CProduct($s2b2cProductId)
    {
        $result = $this->getS2B2CFacadeService()->getS2B2CService()->adoptDirtributeProduct($s2b2cProductId);

        if (!empty($result['status']) && 'success' == $result['status']) {
            $product = $result['data'];
        } else {
            $this->biz->offsetGet('s2b2c.merchant.logger')->error('[adoptProduct] 采用课程失败，原因:'.json_encode($result));
            $this->createNewException(S2B2CProductException::ADOPT_PRODUCT_FAILED());
        }

        //过滤已有的product
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $localProducts = $this->getS2B2CProductDao()->findBySupplierIdAndRemoteProductId($s2b2cConfig['supplierId'], $s2b2cProductId);
        $s2b2cProductDetailIds = array_column($localProducts, 's2b2cProductDetailId');
        $remoteProductDetails = array_filter($product['detail'], function ($detail) use ($s2b2cProductDetailIds) {
            return !in_array($detail['id'], $s2b2cProductDetailIds);
        });

        if (!empty($remoteProductDetails)) {
            $products = array_map(function ($detail) {
                return [
                    's2b2cProductDetailId' => $detail['id'],
                    'supplierId' => $detail['supplierId'],
                    'remoteProductId' => $detail['productId'],
                    'remoteResourceId' => $detail['targetId'],
                    'productType' => $this->getProductType($detail['targetType']),
                    'syncStatus' => 'waiting',
                    'localResourceId' => 0,
                ];
            }, $remoteProductDetails);

            $this->getS2B2CProductDao()->batchCreate($products);
        }

        return true;
    }

    /**
     * @param $s2b2cProductId
     * @param $resourceCourseId
     * @param array $versionData ['title', 'version', 'courseId', 'versionChangeLog']
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function notifyNewVersionProduct($s2b2cProductId, $resourceCourseId, $versionData)
    {
        $product = $this->getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $resourceCourseId, 'course');

        if (!empty($product)) {
            $this->getS2B2CProductDao()->update($product['id'], ['remoteVersion' => $versionData['version']]);
        }
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $courseSetProduct = $this->getProductBySupplierIdAndRemoteProductIdAndType($s2b2cConfig['supplierId'], $s2b2cProductId, 'course_set');

        $localChangeLogs = $courseSetProduct['changelog'] ?: [];
        $localChangeLogs[$versionData['courseId']] = $versionData;

        $this->getS2B2CProductDao()->update($courseSetProduct['id'], ['changelog' => $localChangeLogs]);
        $this->getS2B2CProductDao()->wave([$courseSetProduct['id']], ['remoteVersion' => 1]);

        return true;
    }

    public function findUpdatedVersionProductList()
    {
        return $this->getS2B2CProductDao()->findRemoteVersionGTLocalVersion();
    }

    public function updateProductVersion($id)
    {
        $product = $this->getProduct($id);

        if (empty($product)) {
            $this->createNewException(S2B2CProductException::PRODUCT_NOT_FOUNT());
        }

        $this->adoptS2B2CProduct($product['remoteProductId']);

        $this->beginTransaction();

        try {
            $this->getCourseProductService()->updateProductVersionData($product['remoteProductId']);
            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            $this->getLogger()->error('[updateProductVersion] 更新失败 '.$exception->getMessage());
            $this->createNewException(S2B2CProductException::UPDATE_PRODUCT_VERSION_FAIL());
        }

        return true;
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * 获取远程productType 和 本地productType的映射关系
     *
     * @param $productType
     *
     * @return mixed
     */
    protected function getProductType($productType)
    {
        $type = [
            'courseSet' => 'course_set',
            'course' => 'course',
        ];

        return $type[$productType];
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

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}

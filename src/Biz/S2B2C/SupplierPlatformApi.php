<?php

namespace Biz\S2B2C;

use Codeages\Biz\Framework\Context\Biz;

class SupplierPlatformApi extends AbstractPlatformApi
{
    // 签名允许调用时效
    const LIFE_TIME = 30;

    // 获取供应商产品详情接口路径
    private $productsPath = '/api/merchant/s2b2c/course/s2b2c/product';

    private $courseSetProductPath = '/api/merchant/s2b2c/course_set/s2b2c/product';

    // 获取供应商产品同步信息的接口
    private $productSyncDataPath = '/api/merchant/s2b2c/product/s2b2c/sync/s2b2c/data';

    // 获取已选商品列表接口路径
    private $purchaseProductsPath = '/api/merchant/s2b2c/purchase/s2b2c/product';

    // 获取供应商站点信息
    private $siteSettingPath = '/api/setting/site';

    // 获取供应商产品列表所有分类接口路径
    private $productCategoryPath = '/api/merchant/s2b2c/product/s2b2c/category';

    // 获取供应商的商品更新列表
    private $productVersionListPath = '/api/merchant/s2b2c/product/s2b2c/version/s2b2c/list';

    private $permissionListPath = '/api/merchant/{accesskey}/permission';

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);
        $developerSetting = $this->getSettingService()->get('developer', []);
        if (!empty($developerSetting['s2b2c_supplier_server'])) {
            $this->host = $developerSetting['s2b2c_supplier_server'];
        } else {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            if (empty($s2b2cConfig['enabled'])) {
                $this->getLogger()->error('construct supplierPlatformApi error no S2B2CSettings');
                $this->apiValid = false;

                return false;
            }
            $this->host = $s2b2cConfig['supplierDomain'];
        }
    }

    /**
     * @return string[]
     *                  mocked
     */
    public function getMerchantDisabledPermissions()
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }
        $storageSetting = $this->getSettingService()->get('storage');
        if (empty($storageSetting['cloud_access_key'])) {
            return $this->createErrorResult('accesskey unexist');
        }

        $this->uri = str_replace('{accesskey}', $storageSetting['cloud_access_key'], $this->permissionListPath);

        $options = [
            'connectTimeout' => 300,
            'timeout' => 300,
        ];

        return $this->sendRequest('getMerchantDisabledPermissions', [], $options);
    }

    /**
     * 获取供应商产品详情
     *
     * @return array()
     */
    public function getSupplierProductDetail($distributeId)
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }
        $data = [];
        $this->uri = $this->productsPath.'/'.$distributeId;
        $options = [
            'connectTimeout' => 300,
            'timeout' => 300,
        ];

        return $this->sendRequest('getSupplierProductDetail', $data, $options);
    }

    /**
     * 获取供应商产品同步的内容数据
     *
     * @return array()
     */
    public function getSupplierProductSyncData($distributeId)
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }
        $data = [];
        $this->uri = $this->productSyncDataPath.'/'.$distributeId;
        $options = [
            'connectTimeout' => 300,
            'timeout' => 300,
        ];

        return $this->sendRequest('getSupplierProductSyncData', $data, $options);
    }

    /**
     * 获取供应商产品列表
     *
     * @return array()
     */
    public function searchSupplierProducts($conditions = [])
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }

        $data = $conditions;
        ksort($data);
        $this->uri = $this->productsPath;
        $options = [
            'connectTimeout' => 3,
            'timeout' => 5,
        ];

        try {
            $this->getLogger()->info('try searchSupplierProducts: ', ['DATA' => $data, 'HOST' => $this->host]);
            $result = $this->request('GET', $data, $options);
            $this->getLogger()->info('searchSupplierProducts SUCCEED');
        } catch (\Exception $e) {
            $this->getLogger()->error('searchSupplierProducts error: '.$e->getMessage(), ['DATA' => $data]);

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }

    public function checkPurchaseProducts($purchaseProducts)
    {
        $settings = $this->getSettingService()->get('storage', []);
        $checkPurchaseProductsPath = "/api/merchant/{$settings['cloud_access_key']}/action/check_purchase";

        $this->uri = $checkPurchaseProductsPath;
        $options = [
            'connectTimeout' => 300,
            'timeout' => 300,
            'headers' => [
                'Content-Type: application/json',
            ],
            'params' => 'body',
        ];

        try {
            $this->getLogger()->info('try checkPurchaseProducts: ', ['DATA' => $purchaseProducts]);
            $result = $this->request('GET', $purchaseProducts, $options);
            $this->getLogger()->info('checkPurchaseProducts SUCCEED');
        } catch (\Exception $e) {
            $this->getLogger()->error('checkPurchaseProducts error: '.$e->getMessage(), ['DATA' => $purchaseProducts]);

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }

    /**
     * 获取已选商品列表接口路径
     *
     * @return array()
     */
    public function searchPurchaseProducts($conditions = [])
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }

        $data = $conditions;
        ksort($data);
        $this->uri = $this->purchaseProductsPath;
        $options = [
            'connectTimeout' => 3,
            'timeout' => 5,
        ];

        try {
            $this->getLogger()->info('try searchPurchaseProducts: ', ['DATA' => $data]);
            $result = $this->request('GET', $data, $options);
            $this->getLogger()->info('searchPurchaseProducts SUCCEED');
        } catch (\Exception $e) {
            $this->getLogger()->error('searchPurchaseProducts error: '.$e->getMessage(), ['DATA' => $data]);

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }

    public function getSupplierSiteSetting()
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }

        $this->uri = $this->siteSettingPath;
        $options = [
            'connectTimeout' => 3,
            'timeout' => 5,
        ];

        try {
            $this->getLogger()->info('try getSupplierSiteSetting');
            $result = $this->request('GET', [], $options);
            $this->getLogger()->info('getSupplierSiteSetting SUCCEED');
        } catch (\Exception $e) {
            $this->getLogger()->error('getSupplierSiteSetting error: '.$e->getMessage());

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }

    /**
     * 获取供应商的商品分类接口
     *
     * @return array()
     */
    public function searchProductCategories($conditions = [])
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }

        $data = $conditions;
        ksort($data);
        $this->uri = $this->productCategoryPath;
        $options = [
            'connectTimeout' => 5,
            'timeout' => 5,
        ];

        return $this->sendRequest('searchProductCategories', $data, $options);
    }

    /**
     * 获取供应商的商品更新列表
     *
     * @return array()
     */
    public function getProductVersionList($productIds)
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }
        if (empty($productIds)) {
            return [];
        }

        $data = [
            'productIds' => $productIds,
        ];
        ksort($data);
        $this->uri = $this->productVersionListPath;
        $options = [
            'connectTimeout' => 5,
            'timeout' => 5,
        ];

        return $this->sendRequest('getProductVersionList', $data, $options);
    }

    /**
     * 获取供应商具体商品所有更新日志
     *
     * @return array()
     */
    public function getProductVersions($productId = [])
    {
        if (!$this->apiValid) {
            return $this->createErrorResult();
        }

        $data = [];
        $this->uri = $this->productVersionListPath.'/'.$productId;
        $options = [
            'connectTimeout' => 5,
            'timeout' => 5,
        ];

        return $this->sendRequest('getProductVersions', $data, $options);
    }

    private function sendRequest($action, $data, $options, $requestMethod = 'GET')
    {
        try {
            $this->getLogger()->info('try '.$action.': ', ['DATA' => $data]);
            $result = $this->request($requestMethod, $data, $options);
            if (!empty($result['error']) || (!empty($result['code']) && 200 != $result['code'])) {
                $this->getLogger()->info($action.' 调用接口失败', ['DATA' => $result]);
                throw $this->createServiceException($action.' 调用接口失败');
            }
            $this->getLogger()->info($action.' SUCCEED', [$result]);
        } catch (\Exception $e) {
            $this->getLogger()->error($action.' error: '.$e->getMessage(), ['DATA' => $data]);

            return $this->createErrorResult($e->getMessage());
        }

        return $result;
    }
}

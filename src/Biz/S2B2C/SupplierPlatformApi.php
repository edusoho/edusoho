<?php

namespace Biz\S2B2C;

use Codeages\Biz\Framework\Context\Biz;

class SupplierPlatformApi extends AbstractPlatformApi
{
    // 签名允许调用时效
    const LIFE_TIME = 30;

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

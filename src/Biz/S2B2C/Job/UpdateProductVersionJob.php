<?php

namespace Biz\S2B2C\Job;

use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateProductVersionJob extends AbstractJob
{
    public function execute()
    {
        $products = $this->getProducts();
        $this->biz->offsetGet('s2b2c.merchant.logger')->info('[UpdateProductJob] product', $products);
        foreach ($products as $product) {
            $this->getProductService()->updateProductVersion($product['id']);
        }
    }

    protected function getProducts()
    {
        return $this->getProductService()->findUpdatedVersionProductList();
    }

    /**
     * @return CourseProductService
     */
    protected function getS2B2CCourseProductService()
    {
        return $this->biz->service('S2B2C:CourseProductService');
    }

    /**
     * @return SupplierPlatformApi
     */
    protected function getSupplierPlatformApi()
    {
        return$this->biz->offsetGet('supplier.platform_api');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('S2B2C:ProductService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }
}
